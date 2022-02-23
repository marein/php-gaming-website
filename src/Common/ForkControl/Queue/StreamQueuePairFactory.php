<?php

declare(strict_types=1);

namespace Gaming\Common\ForkControl\Queue;

use Gaming\Common\ForkControl\Exception\ForkControlException;

final class StreamQueuePairFactory implements QueuePairFactory
{
    public function __construct(
        private readonly int $sendReceiveTimeoutInSeconds
    ) {
    }

    public function create(): QueuePair
    {
        $streamPair = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        if ($streamPair === false) {
            throw new ForkControlException(
                error_get_last()['message'] ?? 'Cannot create stream pair.'
            );
        }

        return new QueuePair(
            new StreamQueue($streamPair[0], $this->sendReceiveTimeoutInSeconds),
            new StreamQueue($streamPair[1], $this->sendReceiveTimeoutInSeconds)
        );
    }
}
