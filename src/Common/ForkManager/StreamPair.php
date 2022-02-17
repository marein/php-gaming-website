<?php

declare(strict_types=1);

namespace Gaming\Common\ForkManager;

use Gaming\Common\ForkManager\Exception\ForkManagerException;

final class StreamPair
{
    private function __construct(
        private readonly Stream $parent,
        private readonly Stream $fork
    ) {
    }

    /**
     * @throws ForkManagerException
     */
    public static function create(): StreamPair
    {
        $streamSocketPair = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        if ($streamSocketPair === false) {
            throw new ForkManagerException(
                error_get_last()['message'] ?? 'Cannot create stream pair.'
            );
        }

        return new self(
            new Stream($streamSocketPair[0]),
            new Stream($streamSocketPair[1])
        );
    }

    public function parent(): Stream
    {
        return $this->parent;
    }

    public function fork(): Stream
    {
        return $this->fork;
    }
}
