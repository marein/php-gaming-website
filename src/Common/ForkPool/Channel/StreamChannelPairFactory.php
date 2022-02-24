<?php

declare(strict_types=1);

namespace Gaming\Common\ForkPool\Channel;

use Gaming\Common\ForkPool\Exception\ForkPoolException;

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
            throw new ForkPoolException(
                error_get_last()['message'] ?? 'Cannot create stream pair.'
            );
        }

        return new ChannelPair(
            new StreamChannel($streamPair[0], $this->sendReceiveTimeoutInSeconds),
            new StreamChannel($streamPair[1], $this->sendReceiveTimeoutInSeconds)
        );
    }
}
