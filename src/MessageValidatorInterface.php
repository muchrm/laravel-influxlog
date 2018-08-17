<?php

namespace Muchrm\InfluxLog;

interface MessageValidatorInterface
{
    public function validate(MessageInterface $message, &$reason = '');
}
