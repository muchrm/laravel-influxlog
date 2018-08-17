<?php

namespace Muchrm\InfluxLog\Processor;

use Illuminate\Http\Request;
use Muchrm\InfluxLog\Message;

class RequestProcessor implements ProcessorInterface
{
    public function process(Message $message, $exception, $context)
    {
        // Don't process when the setting is off
        if (!config('influxlog.log_requests', false)) {
            return $message;
        }
        /** @var Request $request */
        $request = app('Illuminate\Http\Request');
        if (!$request) {
            return $message;
        }
        // Add GET data if configured
        if (config('influxlog.log_request_get_data', false)) {
            $message->setAdditional('request_get_data', json_encode($request->query()));
        }
        // Add filtered POST data if configured
        if (config('influxlog.log_request_post_data', false)) {
            $filteredParameters = array_filter(
                $request->request->all(),
                [$this, 'doFilter'],
                ARRAY_FILTER_USE_BOTH
            );
            $message->setAdditional('request_post_data', json_encode($filteredParameters));
        }

        return $message
                ->setAdditional('request_url', $request->url())
                ->setAdditional('request_method', $request->method())
                ->setAdditional('request_ip', $request->ip());
    }

    private function doFilter($vals, $key)
    {
        $disallowedParameters = config('influxlog.disallowed_post_parameters', []);
        $output = true;
        if (is_array($vals)) {
            foreach ($vals as $key=>$val) {
                if (is_array($val)) {
                    if (!$this->doFilter($key, $val)) {
                        $output = false;
                    }
                } else {
                    if (in_array($key, $disallowedParameters)) {
                        $output = false;
                    }
                }
            }
        } else {
            if (in_array($key, $disallowedParameters)) {
                $output = false;
            }
        }

        return $output;
    }
}
