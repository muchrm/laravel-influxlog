<?php

use Muchrm\InfluxLog\Facades\InfluxLog;

include __DIR__.'/TestInfluxLogTransport.php';
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
                'short_message' => 'Test Message',
                'level'         => 0,
                '_a'            => 'true',
            ],
            $message
        );
    }

    public function testTransport()
    {
        // Mock the null transport and add it to the transport stack in the publisher
        $transportStub = $this->getMockBuilder(\Muchrm\InfluxLog\Transport\InfluxClientTransport::class)
            ->setMethods(['send'])
            ->getMock();
        InfluxLog::addTransportToPublisher($transportStub);
        // Expect the stub to be called
        $transportStub->expects($this->once())
            ->method('send')
            ->with($this->isInstanceOf(\Muchrm\InfluxLog\Message::class));
        InfluxLog::log('emergency', 'test', []);
    }

    public function testMessageGeneration()
    {
        $self = $this;
        $testTransport = new TestInfluxLogTransport(function (\Muchrm\InfluxLog\MessageInterface $message) use ($self) {
            $self->assertEquals('test', $message->getShortMessage());
            $self->assertEquals('error', $message->getLevel());
        });
        InfluxLog::addTransportToPublisher($testTransport);
        InfluxLog::log('error', 'test', []);
    }

    public function testException()
    {
        // Set additional fields
        InfluxLog::registerProcessor(new \Muchrm\InfluxLog\Processor\ExceptionProcessor());
        $e = new \Exception('test Exception', 300);
        $self = $this;
        $testTransport = new TestInfluxLogTransport(function (\Muchrm\InfluxLog\MessageInterface $message) use ($self, $e) {
            $self->assertEquals($e->getLine(), $message->getLine());
        });
        InfluxLog::addTransportToPublisher($testTransport);
        InfluxLog::logException($e);
    }

    public function testRequest()
    {
        // Set additional fields
        InfluxLog::registerProcessor(new \Muchrm\InfluxLog\Processor\RequestProcessor());
        $self = $this;
        $testTransport = new TestInfluxLogTransport(function (\Muchrm\InfluxLog\MessageInterface $message) use ($self) {
            $self->assertEquals('http://localhost', $message->getAdditional('request_url'));
            $self->assertEquals('GET', $message->getAdditional('request_method'));
            $self->assertEquals('127.0.0.1', $message->getAdditional('request_ip'));
        });
        InfluxLog::addTransportToPublisher($testTransport);
        InfluxLog::log('error', 'test', [
            'request' => request(),
        ]);
    }

    public function testRequestProcessorParameters()
    {
        InfluxLog::registerProcessor(new \Muchrm\InfluxLog\Processor\RequestProcessor());
        $self = $this;
        $testTransport = new TestInfluxLogTransport(function (\Muchrm\InfluxLog\MessageInterface $message) use ($self) {
            $self->assertEquals('{"test":true}', $message->getAdditional('request_get_data'));
            $self->assertEquals('{"test_post":true}', $message->getAdditional('request_post_data'));
            $self->assertEquals('http://localhost', $message->getAdditional('request_url'));
            $self->assertEquals('GET', $message->getAdditional('request_method'));
            $self->assertEquals('127.0.0.1', $message->getAdditional('request_ip'));
        });
        InfluxLog::addTransportToPublisher($testTransport);
        // Enable get and post data logging
        Config::set('influxlog.log_request_get_data', true);
        Config::set('influxlog.log_request_post_data', true);
        $request = request();
        $request->query->set('test', true);
        $request->request->set('test_post', true);
        // Check if we filter out the username
        $request->request->set('username', 'henk');
        $request->request->set('user', ['password'=>'henk']);
        InfluxLog::log('error', 'test', []);
    }

    public function testRawfMessage()
    {
        // Set additional fields
        $self = $this;
        $testTransport = new TestInfluxLogTransport(function (\Muchrm\InfluxLog\MessageInterface $message) use ($self) {
            $self->assertEquals('Test Message', $message->getShortMessage());
        });
        InfluxLog::addTransportToPublisher($testTransport);
        $message = new \Muchrm\InfluxLog\Message();
        $message->setShortMessage('Test Message');
        InfluxLog::logMessage($message);
    }
}
