# Laravel Queue Monitor

A Laravel package to monitor queue jobs. Logs certain information about [queue jobs](https://laravel.com/docs/5.3/queues) in a database table:

* The elapsed time of the job including start and finish timestamps
* If `--tries` is being used the attempt number for each job
* If the job failed and the exception given (if available)
* Custom data (optional)

## Requirements

* Laravel 5.3+

## Install

Install the composer package:

```
composer require gilbitron/laravel-queue-monitor
```

Add the service provider in `config/app.php`:

```
/*
 * Package Service Providers...
 */
Gilbitron\LaravelQueueMonitor\LaravelQueueMonitorProvider::class,
```

Run a migration to setup the `queue_monitor` database table:

```
php artisan migrate
```

## Usage

All queue jobs will now be monitored and results stored to the `queue_monitor` database table. No other configuration is required.

### Custom Data

To save custom data with the queue monitor results you need to include the `QueueMonitorData` trait in your Job and use the `saveQueueMonitorData()` method. For example:

```
<?php

namespace App\Jobs;

use Gilbitron\LaravelQueueMonitor\Jobs\QueueMonitorData;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ExampleJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels, QueueMonitorData;

    protected $results = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->results = rand(1, 100);

        $this->saveQueueMonitorData([
            'results' => $this->results,
        ]);

        // ...
    }
}
```

## Credits

Laravel Queue Monitor was created by [Gilbert Pellegrom](https://gilbert.pellegrom.me) from
[Dev7studios](https://dev7studios.co). Released under the MIT license.