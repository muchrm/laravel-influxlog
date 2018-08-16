<?php
namespace Muchrm\InfluxLog;
use Psr\Log\LoggerInterface;
use Psr\Log\AbstractLogger;
use Muchrm\InfluxLog\Transport\InfluxClientTransport;
class InfluxLog extends AbstractLogger implements LoggerInterface
{
    protected $transport;
    public function __construct()
    {
        $this->transport = new InfluxClientTransport(
            config('influxlog.connection.host'),
            config('influxlog.connection.port'),
            config('influxlog.connection.db')
        );
    }
    public function log($level, $rawMessage, array $context = array())
    {
        $message = $this->initMessage($level, $rawMessage, $context);
        $this->transport->send($message);
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
        $messageObj->setMessage($message);
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
                        $value = (string)$value;
                    } else {
                        $value = '[object (' . get_class($value) . ')]';
                    }
                    break;
                case 'NULL':
                    $value = 'NULL';
                    break;
                default:
                    $value = '[' . gettype($value) . ']';
                    break;
            }
        }
        return $context;
    }
    private static function interpolate($message, array $context)
    {
        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }
        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }
}