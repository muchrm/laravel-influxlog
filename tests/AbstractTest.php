<?php
abstract class AbstractTest extends Orchestra\Testbench\TestCase
{
    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        // Import default settings
        $defaultLogSettings = require __DIR__.'/../config/influxlog.php';
        $app['config']->set('influxlog', $defaultLogSettings);
    }
    protected function getPackageProviders($app)
    {
        return ['Muchrm\InfluxLog\InfluxLogServiceProvider'];
    }
}