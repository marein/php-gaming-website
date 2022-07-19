<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib;

use Gaming\Common\MessageBroker\Exception\MessageBrokerException;
use Gaming\Common\MessageBroker\Model\Message\Message;
use PhpAmqpLib\Message\AMQPMessage;

interface MessageTranslator
{
    /**
     * @throws MessageBrokerException
     */
    public function createAmqpMessageFromMessage(Message $message): AMQPMessage;
}
