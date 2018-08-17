<?php

namespace Muchrm\InfluxLog;

class MessageValidator implements MessageValidatorInterface
{
    public function validate(MessageInterface $message, &$reason = '')
    {
        if (self::isEmpty($message->getHost())) {
            $reason = 'host not set';

            return false;
        }
        if (self::isEmpty($message->getShortMessage())) {
            $reason = 'short-message not set';

            return false;
        }
        if ($message->hasAdditional('id')) {
            $reason = "additional field 'id' is not allowed";

            return false;
        }

        return true;
    }

    public static function isEmpty($scalar)
    {
        return strlen($scalar) < 1;
    }
}
