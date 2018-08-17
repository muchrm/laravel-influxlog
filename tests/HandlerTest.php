<?php

use Muchrm\InfluxLog\InfluxLogHandler;

class HandlerTest extends AbstractTest
{
    public function testEnabling()
    {
        $handler = new InfluxLogHandler();
        $this->assertFalse($handler->handle([]));
        $this->app['config']->set('influxlog.enabled', false);
        $this->assertFalse($handler->handle([]));
    }
}
