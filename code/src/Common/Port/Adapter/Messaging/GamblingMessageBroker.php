<?php
declare(strict_types=1);

namespace Gambling\Common\Port\Adapter\Messaging;

use Enqueue\AmqpLib\AmqpConnectionFactory;
use Gambling\Common\MessageBroker\Consumer;
use Gambling\Common\MessageBroker\MessageBroker;
use Interop\Amqp\AmqpContext;
use Interop\Amqp\AmqpMessage;
use Interop\Amqp\AmqpQueue;
use Interop\Amqp\AmqpTopic;
use Interop\Amqp\Impl\AmqpBind;

/**
 * This class is used to simplify the interface through an AMQP library in the whole gambling domain.
 */
final class GamblingMessageBroker implements MessageBroker
{
    /**
     * @var string
     */
    private $dsn;

    /**
     * @var bool
     */
    private $isAlreadyInitialized;

    /**
     * @var AmqpContext
     */
    private $context;

    /**
     * @var AmqpTopic
     */
    private $gamblingTopic;

    /**
     * MessageBroker constructor.
     *
     * @param string $dsn
     */
    public function __construct(string $dsn)
    {
        $this->dsn = $dsn;
        $this->isAlreadyInitialized = false;
    }

    /**
     * Used for lazy load the connection.
     */
    private function initialize()
    {
        if (!$this->isAlreadyInitialized) {
            $amqpConnectionFactory = new AmqpConnectionFactory($this->dsn);

            $this->context = $amqpConnectionFactory->createContext();

            $this->gamblingTopic = $this->createTopic('gambling');

            $this->isAlreadyInitialized = true;
        }
    }

    /**
     * Create a durable topic exchange.
     *
     * @param string $name
     *
     * @return AmqpTopic
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
     * Create a durable queue and bind it to gambling exchange via the given routing keys.
     *
     * @param string $name
     * @param array  $routingKeys
     *
     * @return AmqpQueue
     */
    private function createQueue(string $name, array $routingKeys): AmqpQueue
    {
        $queue = $this->context->createQueue($name);
        $queue->addFlag(AmqpQueue::FLAG_DURABLE);
        $this->context->declareQueue($queue);

        foreach ($routingKeys as $routingKey) {
            $this->context->bind(
                new AmqpBind($this->gamblingTopic, $queue, $routingKey)
            );
        }

        return $queue;
    }

    /**
     * @inheritdoc
     */
    public function publish(string $body, string $routingKey): void
    {
        $this->initialize();

        $message = $this->context->createMessage($body);
        $message->addFlag(AmqpMessage::FLAG_MANDATORY);
        $message->setDeliveryMode(AmqpMessage::DELIVERY_MODE_PERSISTENT);
        $message->setRoutingKey($routingKey);

        $producer = $this->context->createProducer();
        $producer->send($this->gamblingTopic, $message);
    }

    /**
     * @inheritdoc
     */
    public function consume(Consumer $consumer): void
    {
        $this->initialize();

        $queue = $this->createQueue(
            $consumer->queueName(),
            $consumer->routingKeys()
        );

        $enqueueConsumer = $this->context->createConsumer(
            $queue
        );

        while (true) {
            $message = $enqueueConsumer->receive(0);

            $consumer->handle(
                $message->getBody(),
                $message->getRoutingKey()
            );

            $enqueueConsumer->acknowledge($message);
        }
    }
}
