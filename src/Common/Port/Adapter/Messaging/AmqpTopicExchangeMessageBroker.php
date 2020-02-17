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
    /**
     * @var string
     */
    private string $dsn;

    /**
     * @var string
     */
    private string $exchangeName;

    /**
     * @var bool
     */
    private bool $isAlreadyInitialized;

    /**
     * @var AmqpContext
     */
    private AmqpContext $context;

    /**
     * @var AmqpTopic
     */
    private AmqpTopic $topic;

    /**
     * AmqpTopicExchangeMessageBroker constructor.
     *
     * @param string $dsn
     * @param string $exchangeName
     */
    public function __construct(string $dsn, string $exchangeName)
    {
        $this->dsn = $dsn;
        $this->exchangeName = $exchangeName;
        $this->isAlreadyInitialized = false;
    }

    /**
     * Used for lazy load the connection.
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
     * Create a durable queue and bind it to the topic exchange via the given routing keys.
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
                new AmqpBind($this->topic, $queue, $routingKey)
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
        $producer->send($this->topic, $amqpMessage);
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
            $message = $enqueueConsumer->receive();

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
