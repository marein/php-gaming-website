<?php

declare(strict_types=1);

namespace Gaming\Common\ForkPool\Channel;

use Gaming\Common\ForkPool\Exception\ForkPoolException;

final class Channels
{
    private int $roundRobinIndex = -1;

    /**
     * @param list<Channel> $channels
     *
     * @throws ForkPoolException
     */
    public function __construct(
        private readonly array $channels
    ) {
        if (count($channels) === 0) {
            throw new ForkPoolException('At least one channel is required.');
        }
    }

    public function random(): Channel
    {
        return $this->channels[array_rand($this->channels)];
    }

    public function roundRobin(): Channel
    {
        return $this->channels[$this->roundRobinIndex = ++$this->roundRobinIndex % count($this->channels)];
    }

    public function consistent(string $key): Channel
    {
        return $this->channels[crc32($key) % count($this->channels)];
    }

    public function synchronize(?int $timeout = null): void
    {
        foreach ($this->channels as $channel) {
            $channel->send(Channel::MESSAGE_SYNC);
        }

        foreach ($this->channels as $channel) {
            if ($channel->receive($timeout) !== Channel::MESSAGE_SYNC_ACK) {
                throw new ForkPoolException('Failed to synchronize all channels.');
            }
        }
    }
}
