<?php

namespace Muchrm\InfluxLog\Transport;

use InfluxDB\Client;
use InfluxDB\Database;
use InfluxDB\Point;
use Muchrm\InfluxLog\MessageInterface;

class InfluxClientTransport implements TransportInterface
{
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = 8086;
    const DEFAULT_DATABASE = 'mydb';
    const DEFAULT_MEASURE = 'user_logs';
    /**
     * @var StreamSocketClient
     */
    protected $socketClient;
    protected $mesure;

    /**
     * Class constructor.
     *
     * @param string $host   when NULL or empty DEFAULT_HOST is used
     * @param int    $port   when NULL or empty DEFAULT_PORT is used
     * @param mixed  $db
     * @param mixed  $mesure
     */
    public function __construct($host = self::DEFAULT_HOST, $port = self::DEFAULT_PORT, $db = self::DEFAULT_DATABASE, $mesure = self::DEFAULT_MEASURE)
    {
        $client = new Client($host, $port);
        $this->mesure = $mesure;
        $this->socketClient = $client->selectDB($db);
    }

    /**
     * Sends a Message over this transport.
     *
     * @param Message $message
     *
     * @return int the number of TCP packets sent
     */
    public function send(MessageInterface $message)
    {
        $tags = array_merge([
                'host' => $message->getHost(),
                'level'=> $message->getLevel(),
            ],
            $message->getAllAdditionals()
        );
        $point = [
            new Point(
                $this->mesure,
                $message->getSyslogLevel(),
                $tags,
                [
                    'short_message' => $message->getShortMessage(),
                    'full_message'  => $message->getFullMessage(), ],
                $message->getTimestamp()
            ),
        ];
        $this->socketClient->writePoints($point, Database::PRECISION_MICROSECONDS);

        return 1;
    }
}
