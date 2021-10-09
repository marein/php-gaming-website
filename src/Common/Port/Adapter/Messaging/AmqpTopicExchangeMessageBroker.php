<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Messaging;

use Enqueue\AmqpLib\AmqpConnectionFactory;
use Gaming\Common\MessageBroker\MessageBroker;
use Gaming\Common\MessageBroker\Model\Consumer\Consumer;
use Gaming\Common\MessageBroker\Model\Message\Message;
use Gaming\Common\MessageBroker\Model\Message\Name;
use Interop\Amqp\AmqpContext;
use Interop\Amqp\AmqpMessage;
use Interop\Amqp\AmqpQueue;
use Interop\Amqp\AmqpTopic;
use Interop\Amqp\Impl\AmqpBind;

final class AmqpTopicExchangeMessageBroker implements MessageBroker
{
    private string $dsn;

    private string $exchangeName;

    private bool $isAlreadyInitialized;

    private AmqpContext $context;

    private AmqpTopic $topic;

    public function __construct(string $dsn, string $exchangeName)
    {
        $this->dsn = $dsn;
        $this->exchangeName = $exchangeName;
        $this->isAlreadyInitialized = false;
    }

    /**
     * Used to lazy load the connection.
     */
    private function initialize(): void
    {
        if (!$this->isAlreadyInitialized) {
            $amqpConnectionFactory = new AmqpConnectionFactory($this->dsn);

            $this->context = $amqpConnectionFactory->createContext();

            $this->topic = $this->createTopic($this->exchangeName);

            $this->isAlreadyInitialized = true;
        }
    }

    /**
     * Create a durable topic exchange.
     */
    private function createTopic(string $name): AmqpTopic
    {
        $topic = $this->context->createTopic($name);
        $topic->addFlag(AmqpTopic::FLAG_DURABLE);
        $topic->setType(AmqpTopic::TYPE_TOPIC);

        $this->context->declareTopic($topic);

        return $topic;
    }

    /**
     * Create a durable queue and bind it to the topic exchange via the given routing keys.
     *
     * @param string[] $routingKeys
     */
    private function createQueue(string $name, array $routingKeys): AmqpQueue
    {
        $queue = $this->context->createQueue($name);
        $queue->addFlag(AmqpQueue::FLAG_DURABLE);
        $this->context->declareQueue($queue);

        foreach ($routingKeys as $routingKey) {
            $this->context->bind(
                new AmqpBind($this->topic, $queue, $routingKey)
            );
        }

        return $queue;
    }

    public function publish(Message $message): void
    {
        $this->initialize();

        $amqpMessage = $this->context->createMessage($message->body());
        $amqpMessage->addFlag(AmqpMessage::FLAG_MANDATORY);
        $amqpMessage->setDeliveryMode(AmqpMessage::DELIVERY_MODE_PERSISTENT);
        $amqpMessage->setRoutingKey((string)$message->name());

        $producer = $this->context->createProducer();
        $producer->send($this->topic, $amqpMessage);
    }

    public function consume(Consumer $consumer): void
    {
        $this->initialize();

        $queue = $this->createQueue(
            sprintf(
                '%s.%s',
                $consumer->name()->domain(),
                $consumer->name()->name()
            ),
            (new SubscriptionsToRoutingKeysTranslator($consumer->subscriptions()))->routingKeys()
        );

        $enqueueConsumer = $this->context->createConsumer(
            $queue
        );

        while (true) {
            $message = $enqueueConsumer->receive();
            assert($message instanceof AmqpMessage);

            $consumer->handle(
                new Message(
                    Name::fromString((string)$message->getRoutingKey()),
                    $message->getBody()
                )
            );

            $enqueueConsumer->acknowledge($message);
        }
    }
}
