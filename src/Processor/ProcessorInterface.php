<?php

namespace Muchrm\InfluxLog\Processor;

use Muchrm\InfluxLog\Message;

interface ProcessorInterface
{
    public function process(Message $message, $exception, $context);
}
