<?php
namespace Muchrm\InfluxLog\Transport;
use Muchrm\InfluxLog\Message;
use InfluxDB\Client;
use InfluxDB\Point;
class InfluxClientTransport
{
    const DEFAULT_HOST = "127.0.0.1";
    const DEFAULT_PORT = 8086;
    const DEFAULT_DATABASE = 'mydb';
    /**
     * @var StreamSocketClient
     */
    protected $socketClient;
    protected $url;
    /**
     * Class constructor
     *
     * @param string $host      when NULL or empty DEFAULT_HOST is used
     * @param int    $port      when NULL or empty DEFAULT_PORT is used
     */
    public function __construct($host = self::DEFAULT_HOST, $port = self::DEFAULT_PORT, $db = self::DEFAULT_DATABASE)
    {
        $client =  new Client($host, $port);
        $this->socketClient = $client->selectDB($db);
    }
    /**
     * Sends a Message over this transport
     *
     * @param Message $message
     *
     * @return int the number of TCP packets sent
     */
    public function send(Message $message)
    {
        $tags = array_merge([
                'host'=>$message->getHost(),
                'level'=>$message->getLevel()
            ],
            $message->getAllAdditionals()
        );
        $point = array(
            new Point(
                'user_log',
                0.64,
                $tags,
                ['message' => $message->getMessage()],
                $message->getTimestamp()
            )
        );
        $this->socketClient->writePoints($point);
        return 1;
    }
}