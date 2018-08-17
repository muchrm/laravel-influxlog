<?php

namespace Muchrm\InfluxLog\Processor;

use Muchrm\InfluxLog\Message;

class ExceptionProcessor implements ProcessorInterface
{
    public function process(Message $message, $exception, $context)
    {
        // Don't process the log when there is no Exception
        if (null === $exception) {
            return $message;
        }
        $message->setLine($exception->getLine());
        $message->setFile($exception->getFile());
        // Check if we want the full stack trace in the message
        if (!config('influxlog.stack_trace_in_full_message', false)) {
            return $message;
        }
        $longText = '';
        do {
            $longText .= sprintf(
                "%s: %s (%d)\n\n%s\n",
                get_class($exception),
                $exception->getMessage(),
                $exception->getCode(),
                $exception->getTraceAsString()
            );
            $exception = $exception->getPrevious();
        } while ($exception && $longText .= "\n--\n\n");
        $message->setFullMessage($longText);

        return $message;
    }
}
