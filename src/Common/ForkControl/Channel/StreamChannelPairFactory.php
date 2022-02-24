<?php

declare(strict_types=1);

namespace Gaming\Common\ForkControl\Channel;

use Gaming\Common\ForkControl\Exception\ForkControlException;

final class StreamChannelPairFactory implements ChannelPairFactory
{
    public function __construct(
        private readonly int $sendReceiveTimeoutInSeconds
    ) {
    }

    public function create(): ChannelPair
    {
        $streamPair = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        if ($streamPair === false) {
            throw new ForkControlException(
                error_get_last()['message'] ?? 'Cannot create stream pair.'
            );
        }

        return new ChannelPair(
            new StreamChannel($streamPair[0], $this->sendReceiveTimeoutInSeconds),
            new StreamChannel($streamPair[1], $this->sendReceiveTimeoutInSeconds)
        );
    }
}
