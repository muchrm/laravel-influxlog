<?php

namespace Muchrm\InfluxLog;

use Psr\Log\LogLevel;
use RuntimeException;

class Message implements MessageInterface
{
    protected $host;
    protected $shortMessage;
    protected $fullMessage;
    protected $timestamp;
    protected $level;
    protected $file;
    protected $line;
    protected $additionals = [];

    private static $psrLevels = [
        LogLevel::EMERGENCY,    // 0
        LogLevel::ALERT,        // 1
        LogLevel::CRITICAL,     // 2
        LogLevel::ERROR,        // 3
        LogLevel::WARNING,      // 4
        LogLevel::NOTICE,       // 5
        LogLevel::INFO,         // 6
        LogLevel::DEBUG,         // 7
    ];

    /**
     * Creates a new message.
     *
     * Populates timestamp and host with sane default values
     */
    public function __construct()
    {
        list($usec, $sec) = explode(' ', microtime());
        $this->timestamp = sprintf('%d%06d', $sec, $usec * 1000000);
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

    public function getShortMessage()
    {
        return $this->shortMessage;
    }

    public function setShortMessage($shortMessage)
    {
        $this->shortMessage = $shortMessage;

        return $this;
    }

    public function getFullMessage()
    {
        return $this->fullMessage;
    }

    public function setFullMessage($fullMessage)
    {
        $this->fullMessage = $fullMessage;

        return $this;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function setTimestamp($timestamp)
    {
        if ($timestamp instanceof \DateTime || $timestamp instanceof \DateTimeInterface) {
            $timestamp = $timestamp->format('Uu');
        }
        $this->timestamp = $timestamp;

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

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    public function getLine()
    {
        return $this->line;
    }

    public function setLine($line)
    {
        $this->line = $line;

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
            throw new RuntimeException('Additional field key cannot be empty');
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
        $message = [
            'host'          => $this->getHost(),
            'short_message' => $this->getShortMessage(),
            'full_message'  => $this->getFullMessage(),
            'level'         => $this->getSyslogLevel(),
            'timestamp'     => $this->getTimestamp(),
            'file'          => $this->getFile(),
            'line'          => $this->getLine(),
        ];
        // add additionals
        foreach ($this->getAllAdditionals() as $key => $value) {
            $message['_'.$key] = $value;
        }
        // return after filtering empty strings and null values
        return array_filter($message, function ($message) {
            return is_bool($message) || strlen($message);
        });
    }
}
