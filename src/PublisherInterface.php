<?php

namespace Muchrm\InfluxLog;

interface PublisherInterface
{
    public function publish(MessageInterface $message);
}
