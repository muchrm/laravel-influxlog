<?php

namespace Muchrm\InfluxLog;

use Illuminate\Support\Facades\Log;
use Monolog\Handler\AbstractHandler;

class InfluxLogHandler extends AbstractHandler
{
    /**
     * {@inheritdoc}
     */
    public function handle(array $record)
    {
        if (!config('influxlog.enabled')) {
            return false;
        }
        $influxlog = app('influxlog');
        try {
            $influxlog->log(
                strtolower($record['level_name']),
                $record['message'],
                $record['context']
            );

            return false;
        } catch (\Exception $e) {
            Log::info('Could not log to InfluxLog.');

            return false;
        }
    }
}
