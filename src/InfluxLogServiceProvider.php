<?php

namespace Muchrm\InfluxLog;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class InfluxLogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        // Publish configuration file
        $this->publishes([
            __DIR__.'/../config/influxlog.php' => $this->app->configPath().'/influxlog.php',
        ]);
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton('influxlog', InfluxLog::class);
        // Register handler
        $monoLog = Log::getMonolog();
        $monoLog->pushHandler(new InfluxLogHandler());
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['influxlog'];
    }
}
