<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\Integration\ForkPool;

use Gaming\Common\EventStore\EventStorePointerFactory;
use Gaming\Common\EventStore\Exception\EventStoreException;
use Gaming\Common\EventStore\FollowEventStoreDispatcher;
use Gaming\Common\EventStore\InMemoryCacheEventStorePointer;
use Gaming\Common\EventStore\PollableEventStore;
use Gaming\Common\ForkPool\Channel\Channel;
use Gaming\Common\ForkPool\Task;
use InvalidArgumentException;

final class Publisher implements Task
{
    /**
     * @param Channel[] $channels
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        private readonly array $channels,
        private readonly PollableEventStore $pollableEventStore,
        private readonly EventStorePointerFactory $eventStorePointerFactory,
        private readonly string $eventStorePointerName,
        private readonly int $throttleTimeInMicroseconds,
        private readonly int $batchSize
    ) {
        if ($this->throttleTimeInMicroseconds < 1000) {
            throw new InvalidArgumentException('throttleTimeInMicroseconds must be at least 1000');
        }
    }

    public function execute(Channel $channel): int
    {
        $followEventStoreDispatcher = new FollowEventStoreDispatcher(
            new ForwardToChannelStoredEventSubscriber($this->channels),
            new InMemoryCacheEventStorePointer(
                $this->eventStorePointerFactory->withName(
                    $this->eventStorePointerName
                )
            ),
            $this->pollableEventStore
        );

        $shouldStop = false;
        pcntl_async_signals(true);
        pcntl_signal(SIGTERM, static function () use (&$shouldStop): void {
            $shouldStop = true;
        });

        while (!$shouldStop) {
            if ($followEventStoreDispatcher->dispatch($this->batchSize) === 0) {
                $this->sendToChannels('KEEPALIVE');
                usleep($this->throttleTimeInMicroseconds);
            }
        }

        $this->sendToChannels('STOP');

        return 0;
    }

    private function sendToChannels(string $message): void
    {
        foreach ($this->channels as $channel) {
            $channel->send($message);
        }
    }
}
