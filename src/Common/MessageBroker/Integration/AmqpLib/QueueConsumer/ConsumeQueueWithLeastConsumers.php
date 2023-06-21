<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\QueueConsumer;

use Gaming\Common\MessageBroker\Integration\AmqpLib\Topology\DefinesQueues;
use Gaming\Common\MessageBroker\MessageHandler;
use PhpAmqpLib\Channel\AMQPChannel;
use Symfony\Component\Lock\LockFactory;

final class ConsumeQueueWithLeastConsumers implements QueueConsumer
{
    public function __construct(
        private readonly DefinesQueues $definesQueues,
        private readonly LockFactory $lockFactory,
        private readonly string $lockName
    ) {
    }

    public function register(AMQPChannel $channel, CallbackFactory $callbackFactory): void
    {
        $lock = $this->lockFactory->createLock($this->lockName . '.lock');
        $lock->acquire(true);

        try {
            $queueName = $this->selectQueueWithLeastConsumers($channel);
            $channel->basic_consume(
                queue: $queueName,
                callback: $callbackFactory->create($queueName)
            );
        } finally {
            $lock->release();
        }
    }

    private function selectQueueWithLeastConsumers(AMQPChannel $channel): string
    {
        $numberOfConsumersPerQueue = [];

        foreach ($this->definesQueues->queueNames() as $queueName) {
            $numberOfConsumersPerQueue[$queueName] = ($channel->queue_declare($queueName, true) ?? [0, 0, 0])[2];

            if ($numberOfConsumersPerQueue[$queueName] === 0) {
                return $queueName;
            }
        }

        asort($numberOfConsumersPerQueue);

        return (string)array_key_first($numberOfConsumersPerQueue);
    }
}
