<?php

declare(strict_types=1);

namespace Gaming\Common\ForkPool\Channel;

use Gaming\Common\ForkPool\Exception\ForkPoolException;

final class StreamChannelPairFactory implements ChannelPairFactory
{
    public function create(): ChannelPair
    {
        $streamPair = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        if ($streamPair === false) {
            throw new ForkPoolException(
                error_get_last()['message'] ?? 'Cannot create stream pair.'
            );
        }

        return new ChannelPair(
            new StreamChannel($streamPair[0]),
            new StreamChannel($streamPair[1])
        );
    }
}
