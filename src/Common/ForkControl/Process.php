<?php

declare(strict_types=1);

namespace Gaming\Common\ForkControl;

use Gaming\Common\ForkControl\Exception\ForkControlException;

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
     * @throws ForkControlException
     */
    public function send(mixed $data): void
    {
        $this->stream->write(serialize($data));
    }

    /**
     * @throws ForkControlException
     */
    public function receive(): mixed
    {
        return unserialize($this->stream->read());
    }
}
