<?php

namespace Gilbitron\LaravelQueueMonitor\Jobs;

use DB;

trait QueueMonitorData
{
    /**
     * Save any extra data to be stored with queue monitor results
     *
     * @param mixed $data
     */
    public function saveQueueMonitorData($data)
    {
        if (!$data || empty($data) || !isset($this->job) || !$this->job) {
            return;
        }

        $jobId = sha1($this->job->getRawBody());
        if (method_exists($this->job, 'getJobId') && $this->job->getJobId()) {
            $jobId = $this->job->getJobId();
        }

        $queueMonitor = DB::table('queue_monitor')
                          ->where('job_id', $jobId)
                          ->orderBy('started_at', 'desc')
                          ->limit(1)
                          ->first();

        if (!$queueMonitor) {
            return;
        }

        DB::table('queue_monitor')->where('id', $queueMonitor->id)->update([
            'data' => serialize($data),
        ]);
    }
}