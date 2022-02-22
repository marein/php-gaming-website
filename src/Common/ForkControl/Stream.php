<?php

declare(strict_types=1);

namespace Gaming\Common\ForkControl;

use Gaming\Common\ForkControl\Exception\ForkControlException;

final class Stream
{
    /**
     * @param resource $resource
     */
    public function __construct(
        private $resource
    ) {
    }

    /**
     * @throws ForkControlException
     */
    public function read(): string
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

        return base64_decode($message);
    }

    /**
     * @throws ForkControlException
     */
    public function write(string $data): void
    {
        $message = base64_encode($data) . "\n";

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

    /**
     * @throws ForkControlException
     */
    public function close(): void
    {
        if (!@fclose($this->resource)) {
            throw new ForkControlException(
                'Cannot close stream.'
            );
        }
    }
}
