<?php

namespace Muchrm\InfluxLog;

use Muchrm\InfluxLog\MessageValidator as DefaultMessageValidator;
use Muchrm\InfluxLog\Transport\TransportInterface;
use RuntimeException;
use SplObjectStorage as Set;

class Publisher implements PublisherInterface
{
    protected $transports;
    protected $messageValidator;

    public function __construct(
        TransportInterface $transport = null,
        MessageValidatorInterface $messageValidator = null
    ) {
        $this->transports = new Set();
        $this->messageValidator = $messageValidator
            ?: new DefaultMessageValidator();
        if (null !== $transport) {
            $this->addTransport($transport);
        }
    }

    public function publish(MessageInterface $message)
    {
        if (0 == count($this->transports)) {
            throw new RuntimeException(
                'Publisher requires at least one transport'
            );
        }
        $reason = '';
        if (!$this->messageValidator->validate($message, $reason)) {
            throw new RuntimeException("Message is invalid: $reason");
        }
        foreach ($this->transports as $transport) {
            /* @var $transport TransportInterface */
            $transport->send($message);
        }
    }

    public function addTransport(TransportInterface $transport)
    {
        $this->transports->attach($transport);
    }

    public function getTransports()
    {
        return iterator_to_array($this->transports);
    }

    public function getMessageValidator()
    {
        return $this->messageValidator;
    }
}
