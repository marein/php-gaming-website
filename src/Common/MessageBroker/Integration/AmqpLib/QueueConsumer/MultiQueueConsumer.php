<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\QueueConsumer;

use Gaming\Common\MessageBroker\Integration\AmqpLib\Topology\MultiQueueTopology;
use Gaming\Common\MessageBroker\MessageHandler;
use Generator;
use PhpAmqpLib\Channel\AMQPChannel;
use Symfony\Component\Lock\LockFactory;

/**
 * This queue consumer can be used with a MultiQueueTopology. It consumes
 * from the queue with the least number of consumers. To consume from all
 * queues, multiple instances of this queue consumer must be started. Scaling up
 * to more consumers should be no problem. Care must be taken when scaling down,
 * as some queues are unlikely to be consumed.
 */
final class MultiQueueConsumer implements QueueConsumer
{
    public function __construct(
        private readonly MessageHandler $messageHandler,
        private readonly MultiQueueTopology $topology,
        private readonly LockFactory $lockFactory
    ) {
    }

    public function register(AMQPChannel $channel, CallbackFactory $callbackFactory): void
    {
        $lock = $this->lockFactory->createLock(get_class($this->messageHandler) . '.lock');
        $lock->acquire(true);

        try {
            $queueName = $this->selectQueueWithLeastConsumers($channel);
            $channel->basic_consume(
                queue: $queueName,
                callback: $callbackFactory->create($queueName, $this->messageHandler)
            );
        } finally {
            $lock->release();
        }
    }

    private function selectQueueWithLeastConsumers(AMQPChannel $channel): string
    {
        $numberOfConsumersPerQueue = [];

        foreach ($this->topology->queueNames() as $queueName) {
            $numberOfConsumersPerQueue[$queueName] = ($channel->queue_declare($queueName, true) ?? [0, 0, 0])[2];

            if ($numberOfConsumersPerQueue[$queueName] === 0) {
                return $queueName;
            }
        }

        asort($numberOfConsumersPerQueue);

        return (string)array_key_first($numberOfConsumersPerQueue);
    }
}
