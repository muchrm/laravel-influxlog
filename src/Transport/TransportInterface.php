<?php

namespace Muchrm\InfluxLog\Transport;

use Muchrm\InfluxLog\MessageInterface;

interface TransportInterface
{
    public function send(MessageInterface $message);
}
