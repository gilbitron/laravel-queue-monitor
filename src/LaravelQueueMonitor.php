<?php

namespace Gilbitron\LaravelQueueMonitor;

use Carbon\Carbon;
use DB;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Queue\QueueManager;

class LaravelQueueMonitor
{
    public function register()
    {
        app(QueueManager::class)->before(function (JobProcessing $event) {
            $this->handleJobProcessing($event);
        });
        app(QueueManager::class)->after(function (JobProcessed $event) {
            $this->handleJobProcessed($event);
        });
        app(QueueManager::class)->failing(function (JobFailed $event) {
            $this->handleJobFailed($event);
        });
        app(QueueManager::class)->exceptionOccurred(function (JobExceptionOccurred $event) {
            $this->handleJobExceptionOccurred($event);
        });
    }

    protected function handleJobProcessing(JobProcessing $event)
    {
        $this->jobStarted($event->job);
    }

    protected function handleJobProcessed(JobProcessed $event)
    {
        $this->jobFinished($event->job);
    }

    protected function handleJobFailed(JobFailed $event)
    {
        $this->jobFinished($event->job, true);
    }

    protected function handleJobExceptionOccurred(JobExceptionOccurred $event)
    {
        $this->jobFinished($event->job, true, $event->exception);
    }

    protected function getJobId(Job $job)
    {
        if (method_exists($job, 'getJobId') && $job->getJobId()) {
            return $job->getJobId();
        }

        return sha1($job->getRawBody());
    }

    protected function jobStarted(Job $job)
    {
        DB::table('queue_monitor')->insert([
            'job_id'     => $this->getJobId($job),
            'name'       => $job->resolveName(),
            'queue'      => $job->getQueue(),
            'started_at' => Carbon::now(),
        ]);
    }

    protected function jobFinished(Job $job, $failed = false, $exception = null)
    {
        $queueMonitor = DB::table('queue_monitor')
                          ->where('job_id', $this->getJobId($job))
                          ->orderBy('started_at', 'desc')
                          ->limit(1)
                          ->first();

        if (!$queueMonitor) {
            return;
        }

        $now         = Carbon::now();
        $timeElapsed = Carbon::parse($queueMonitor->started_at)->diff($now);

        DB::table('queue_monitor')->where('id', $queueMonitor->id)->update([
            'finished_at'  => $now,
            'time_elapsed' => $timeElapsed->s,
            'failed'       => $failed,
            'attempt'      => $job->attempts(),
            'exception'    => $exception ? $exception->getMessage() : null,
        ]);
    }
}