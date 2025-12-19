<?php

declare(strict_types=1);

namespace Gaming\Common\ForkPool\Channel;

use Gaming\Common\ForkPool\Exception\ForkPoolException;

final class NullChannelPairFactory implements ChannelPairFactory
{
    public function create(): ChannelPair
    {
        return new ChannelPair(
            $this->createNullChannel(),
            $this->createNullChannel()
        );
    }

    private function createNullChannel(): Channel
    {
        return new class implements Channel
        {
            public function send(mixed $message): void
            {
                throw new ForkPoolException('Cannot send to null channel.');
            }

            public function receive(?int $timeout = null): mixed
            {
                throw new ForkPoolException('Cannot receive from null channel.');
            }
        };
    }
}
