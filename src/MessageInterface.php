<?php

namespace Muchrm\InfluxLog;

interface MessageInterface
{
    public function getHost();

    public function getShortMessage();

    public function getFullMessage();

    public function getTimestamp();

    public function getLevel();

    public function getSyslogLevel();

    public function getFile();

    public function getLine();

    public function getAdditional($key);

    public function hasAdditional($key);

    public function getAllAdditionals();

    public function toArray();
}
