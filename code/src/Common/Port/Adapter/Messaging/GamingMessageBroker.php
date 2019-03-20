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

/**
 * This class is used to simplify the interface through an AMQP library in the whole gaming domain.
 */
final class GamingMessageBroker implements MessageBroker
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
    private $gamingTopic;

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

            $this->gamingTopic = $this->createTopic('gaming');

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
     * Create a durable queue and bind it to gaming exchange via the given routing keys.
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
                new AmqpBind($this->gamingTopic, $queue, $routingKey)
            );
        }

        return $queue;
    }

    /**
     * @inheritdoc
     */
    public function publish(Message $message): void
    {
        $this->initialize();

        $amqpMessage = $this->context->createMessage($message->body());
        $amqpMessage->addFlag(AmqpMessage::FLAG_MANDATORY);
        $amqpMessage->setDeliveryMode(AmqpMessage::DELIVERY_MODE_PERSISTENT);
        $amqpMessage->setRoutingKey((string)$message->name());

        $producer = $this->context->createProducer();
        $producer->send($this->gamingTopic, $amqpMessage);
    }

    /**
     * @inheritdoc
     */
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
            $message = $enqueueConsumer->receive(0);

            $consumer->handle(
                new Message(
                    Name::fromString($message->getRoutingKey()),
                    $message->getBody()
                )
            );

            $enqueueConsumer->acknowledge($message);
        }
    }
}
