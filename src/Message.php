<?php
namespace Muchrm\InfluxLog;

use Psr\Log\LogLevel;
use RuntimeException;
class Message
{

    protected $host;
    protected $message;
    protected $timestamp;
    protected $level;
    protected $additionals = array();

    private static $psrLevels = array(
        LogLevel::EMERGENCY,    // 0
        LogLevel::ALERT,        // 1
        LogLevel::CRITICAL,     // 2
        LogLevel::ERROR,        // 3
        LogLevel::WARNING,      // 4
        LogLevel::NOTICE,       // 5
        LogLevel::INFO,         // 6
        LogLevel::DEBUG         // 7
    );

    /**
     * Creates a new message
     *
     * Populates timestamp and host with sane default values
     */
    public function __construct()
    {
        $this->timestamp = round(microtime(true));
        $this->host = gethostname();
        $this->level = 1; //ALERT
    }
    final public static function logLevelToPsr($level)
    {
        $origLevel = $level;

        if (is_numeric($level)) {
            $level = intval($level);
            if (isset(self::$psrLevels[$level])) {
                return self::$psrLevels[$level];
            }
        } elseif (is_string($level)) {
            $level = strtolower($level);
            if (in_array($level, self::$psrLevels)) {
                return $level;
            }
        }

        throw new RuntimeException(
            sprintf("Cannot convert log-level '%s' to psr-style", $origLevel)
        );
    }
    final public static function logLevelToSyslog($level)
    {
        $origLevel = $level;

        if (is_numeric($level)) {
            $level = intval($level);
            if ($level < 8 && $level > -1) {
                return $level;
            }
        } elseif (is_string($level)) {
            $level = strtolower($level);
            $syslogLevel = array_search($level, self::$psrLevels);
            if (false !== $syslogLevel) {
                return $syslogLevel;
            }
        }

        throw new RuntimeException(
            sprintf("Cannot convert log-level '%s' to syslog-style", $origLevel)
        );
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    public function getTimestamp()
    {
        return (float) $this->timestamp;
    }

    public function setTimestamp($timestamp)
    {
        if ($timestamp instanceof \DateTime || $timestamp instanceof \DateTimeInterface) {
            $timestamp = $timestamp->format("U.u");
        }

        $this->timestamp = (float) $timestamp;

        return $this;
    }

    public function getLevel()
    {
        return self::logLevelToPsr($this->level);
    }

    public function getSyslogLevel()
    {
        return self::logLevelToSyslog($this->level);
    }

    public function setLevel($level)
    {
        $this->level = self::logLevelToSyslog($level);

        return $this;
    }

    public function getAdditional($key)
    {
        if (!isset($this->additionals[$key])) {
            throw new RuntimeException(
                sprintf("Additional key '%s' is not defined", $key)
            );
        }

        return $this->additionals[$key];
    }

    public function hasAdditional($key)
    {
        return isset($this->additionals[$key]);
    }

    public function setAdditional($key, $value)
    {
        if (!$key) {
            throw new RuntimeException("Additional field key cannot be empty");
        }

        $this->additionals[$key] = $value;

        return $this;
    }

    public function getAllAdditionals()
    {
        return $this->additionals;
    }

    public function toArray()
    {
        $message = array(
            'host'          => $this->getHost(),
            'message' => $this->getMessage(),
            'level'         => $this->getSyslogLevel(),
            'timestamp'     => $this->getTimestamp(),
        );

        // add additionals
        foreach ($this->getAllAdditionals() as $key => $value) {
            $message["_" . $key] = $value;
        }

        // return after filtering empty strings and null values
        return array_filter($message, function ($message) {
            return is_bool($message) || strlen($message);
        });
    }
}