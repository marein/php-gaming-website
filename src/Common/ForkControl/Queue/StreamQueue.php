<?php

declare(strict_types=1);

namespace Gaming\Common\ForkControl\Queue;

use Gaming\Common\ForkControl\Exception\ForkControlException;

final class StreamQueue implements Queue
{
    /**
     * @param resource $resource
     */
    public function __construct(
        private $resource
    ) {
    }

    public function send(mixed $message): void
    {
        $message = base64_encode(serialize($message)) . "\n";

        $numberOfWrittenBytes = @fwrite($this->resource, $message);
        if ($numberOfWrittenBytes !== strlen($message)) {
            throw new ForkControlException(
                sprintf(
                    'Expected to write %s bytes, but %s bytes were written.',
                    strlen($message),
                    (int)$numberOfWrittenBytes
                )
            );
        }
    }

    public function receive(): mixed
    {
        $message = @fgets($this->resource);
        if ($message === false || !str_ends_with($message, "\n")) {
            throw new ForkControlException(
                sprintf(
                    'Message not arrived completely. Received %s bytes.',
                    $message === false ? '0' : strlen($message)
                )
            );
        }

        return unserialize(base64_decode($message));
    }

    public function __destruct()
    {
        @fclose($this->resource);
    }
}
