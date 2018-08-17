<?php

class TestInfluxLogTransport implements \Muchrm\InfluxLog\Transport\TransportInterface
{
    private $callback;

    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    public function send(\Muchrm\InfluxLog\MessageInterface $message)
    {
        $this->callback->__invoke($message);

        return 1;
    }
}
