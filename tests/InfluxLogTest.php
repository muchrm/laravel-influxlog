<?php
class LoggerTest extends AbstractTest
{
    /**
     * Test a simple log message.
     */
    public function testSimplePreparation()
    {
        $logger = new \Muchrm\InfluxLog\InfluxLog();
        $message = $logger->log('emergency', 'Test Message', ['a' => true])->toArray();
        // Ignore timestamp
        unset($message['timestamp']);
        // Ignore host (we don't know the host on Travis)
        unset($message['host']);
        $this->assertEquals(
            [
                'message' => 'Test Message',
                'level'         => 0,
                '_a'            => 'true',
            ],
            $message
        );
    }
}