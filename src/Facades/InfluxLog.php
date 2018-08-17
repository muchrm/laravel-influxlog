<?php

namespace Muchrm\InfluxLog\Facades;

use Illuminate\Support\Facades\Facade;

class InfluxLog extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'influxlog';
    }
}
