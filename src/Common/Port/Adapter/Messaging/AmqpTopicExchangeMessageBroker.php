<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Messaging;

use Enqueue\AmqpLib\AmqpConnectionFactory;
use Gaming\Common\MessageBroker\MessageBroker;
use Gaming\Common\MessageBroker\Model\Consumer\Consumer;
use Gaming\Common\MessageBroker\Model\Context\Context;
use Gaming\Common\MessageBroker\Model\Message\Message;
use Gaming\Common\MessageBroker\Model\Message\Name;
use Interop\Amqp\AmqpContext;
use Interop\Amqp\AmqpMessage;
use Interop\Amqp\AmqpQueue;
use Interop\Amqp\AmqpTopic;
use Interop\Amqp\Impl\AmqpBind;
use Interop\Amqp\Impl\AmqpQueue as AmqpQueueImpl;
use Interop\Queue\Consumer as InteropConsumer;
use Interop\Queue\Destination;
use Interop\Queue\SubscriptionConsumer;

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

        $this->sendMessage($this->topic, $message, null);
    }

    public function consume(iterable $consumers): void
    {
        $this->initialize();

        $subscriptionConsumer = $this->context->createSubscriptionConsumer();
        foreach ($consumers as $consumer) {
            $this->addConsumerToSubscription($subscriptionConsumer, $consumer);
        }
        $subscriptionConsumer->consume();
    }

    private function addConsumerToSubscription(SubscriptionConsumer $subscriptionConsumer, Consumer $consumer): void
    {
        $queueName = $consumer->name()->domain() . '.' . $consumer->name()->name();

        $subscriptionConsumer->subscribe(
            $this->context->createConsumer(
                $this->createQueue(
                    $queueName,
                    (new SubscriptionsToRoutingKeysTranslator($consumer->subscriptions()))->routingKeys()
                )
            ),
            function (AmqpMessage $message, InteropConsumer $interopConsumer) use ($consumer, $queueName): void {
                [$name, $body] = json_decode($message->getBody(), true, 512, JSON_THROW_ON_ERROR);

                $consumer->handle(
                    new Message(
                        Name::fromString($name),
                        $body
                    ),
                    $this->createContext($queueName, $message)
                );

                $interopConsumer->acknowledge($message);
            }
        );
    }

    private function createContext(string $queueName, AmqpMessage $amqpMessage): Context
    {
        return new ClosureContext(
            fn(Message $message) => $this->sendMessage(
                $this->topic,
                $message,
                $queueName
            ),
            $amqpMessage->getReplyTo() === null ?
                function (Message $message) {
                } :
                fn(Message $message) => $this->sendMessage(
                    new AmqpQueueImpl($amqpMessage->getReplyTo()),
                    $message,
                    null
                )
        );
    }

    private function sendMessage(Destination $destination, Message $message, ?string $replyTo): void
    {
        $amqpMessage = $this->context->createMessage(
            json_encode(
                [(string)$message->name(), $message->body()],
                JSON_THROW_ON_ERROR
            )
        );
        $amqpMessage->addFlag(AmqpMessage::FLAG_MANDATORY);
        $amqpMessage->setDeliveryMode(AmqpMessage::DELIVERY_MODE_PERSISTENT);
        $amqpMessage->setRoutingKey((string)$message->name());
        $amqpMessage->setReplyTo($replyTo);

        $this->context->createProducer()->send(
            $destination,
            $amqpMessage
        );
    }
}
