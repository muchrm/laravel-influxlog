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
    const DEFAULT_MEASURE = 'user_log';
    /**
     * @var StreamSocketClient
     */
    protected $socketClient;
    protected $mesure;
    /**
     * Class constructor
     *
     * @param string $host      when NULL or empty DEFAULT_HOST is used
     * @param int    $port      when NULL or empty DEFAULT_PORT is used
     */
    public function __construct($host = self::DEFAULT_HOST, $port = self::DEFAULT_PORT, $db = self::DEFAULT_DATABASE, $mesure = self::DEFAULT_MEASURE)
    {
        $client =  new Client($host, $port);
        $this->mesure = $mesure;
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
                $this->mesure,
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