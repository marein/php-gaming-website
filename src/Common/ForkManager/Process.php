<?php

declare(strict_types=1);

namespace Gaming\Common\ForkManager;

use Gaming\Common\ForkManager\Exception\ForkManagerException;

final class Process
{
    public function __construct(
        private readonly int $processId,
        private readonly Stream $stream
    ) {
    }

    public function terminate(): void
    {
        posix_kill($this->processId, SIGTERM);
    }

    /**
     * @throws ForkManagerException
     */
    public function send(mixed $data): void
    {
        $this->stream->write(base64_encode(serialize($data)));
    }

    /**
     * @throws ForkManagerException
     */
    public function receive(): mixed
    {
        return unserialize(base64_decode($this->stream->read()));
    }
}
