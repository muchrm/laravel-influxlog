<?php

namespace Muchrm\InfluxLog;

use Muchrm\InfluxLog\Processor\ProcessorInterface;
use Muchrm\InfluxLog\Transport\InfluxClientTransport;
use Muchrm\InfluxLog\Transport\TransportInterface;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class InfluxLog extends AbstractLogger implements LoggerInterface
{
    protected $publisher;
    protected $processors = [];

    public function __construct()
    {
        $this->publisher = new Publisher();
        $this->publisher->addTransport(new InfluxClientTransport(
            config('influxlog.connection.host'),
            config('influxlog.connection.port'),
            config('influxlog.connection.db'),
            config('influxlog.connection.measure')
        ));
    }

    public function log($level, $rawMessage, array $context = [])
    {
        $message = $this->initMessage($level, $rawMessage, $context);
        $exception = null;
        if (array_key_exists('exception', $context)) {
            $exception = $context['exception'];
        }
        $message = $this->invokeProcessors($message, $exception, $context);
        $this->publisher->publish($message);

        return $message;
    }

    protected function initMessage($level, $message, array $context)
    {
        // assert that message is a string, and interpolate placeholders
        $message = (string) $message;
        $context = $this->initContext($context);
        $message = self::interpolate($message, $context);
        // create message object
        $messageObj = new Message();
        $messageObj->setLevel($level);
        $messageObj->setShortMessage($message);
        foreach ($context as $key => $value) {
            $messageObj->setAdditional($key, $value);
        }

        return $messageObj;
    }

    protected function initContext($context)
    {
        foreach ($context as $key => &$value) {
            switch (gettype($value)) {
                case 'string':
                case 'integer':
                case 'double':
                    // These types require no conversion
                    break;
                case 'array':
                case 'boolean':
                    $value = json_encode($value);
                    break;
                case 'object':
                    if (method_exists($value, '__toString')) {
                        $value = $this->TextEncode((string)$value);
                    } else {
                        $value = '[object ('.get_class($value).')]';
                    }
                    break;
                case 'NULL':
                    $value = 'NULL';
                    break;
                default:
                    $value = '['.gettype($value).']';
                    break;
            }
        }

        return $context;
    }

    private function TextEncode($string)
    {
        $entities = [',', ' ', "\r", "\n"];
        $replacements = ['%2C', '', '%2a'];

        return str_replace($entities, $replacements, $string);
    }

    public function logException(\Exception $exception)
    {
        // Set short-message as it is a requirement
        $message = new Message();
        $message->setShortMessage(substr($exception->getMessage(), 0, 100));
        $message = $this->invokeProcessors($message, $exception);
        $this->publisher->publish($message);
    }

    public function logMessage(Message $message)
    {
        $message = $this->invokeProcessors($message);
        $this->publisher->publish($message);
    }

    public function addTransportToPublisher(TransportInterface $transport)
    {
        $this->publisher->addTransport($transport);
    }

    public function registerProcessor(ProcessorInterface $processor)
    {
        $this->processors[] = $processor;
    }

    private function invokeProcessors(Message $message, $exception = null, $context = [])
    {
        foreach ($this->processors as $processor) {
            $message = $processor->process($message, $exception, $context);
        }

        return $message;
    }

    private static function interpolate($message, array $context)
    {
        // build a replacement array with braces around the context keys
        $replace = [];
        foreach ($context as $key => $val) {
            $replace['{'.$key.'}'] = $val;
        }
        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }
}
