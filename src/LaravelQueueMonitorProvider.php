<?php

namespace Gilbitron\LaravelQueueMonitor;

use Illuminate\Support\ServiceProvider;

class LaravelQueueMonitorProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $basePath = __DIR__ . '/..';

        $this->loadMigrationsFrom($basePath . '/migrations');

        $this->app->make(LaravelQueueMonitor::class)->register();
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}