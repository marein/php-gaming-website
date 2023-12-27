<?php

declare(strict_types=1);

namespace Gaming\Common\ForkPool\Channel;

use Gaming\Common\ForkPool\Exception\ForkPoolException;

final class StreamChannel implements Channel
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
            throw new ForkPoolException(
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
        while (!$this->canReadFromResource());

        $message = @fgets($this->resource);
        if ($message === false || !str_ends_with($message, "\n")) {
            throw new ForkPoolException(
                sprintf(
                    'Message not arrived completely. Received %s bytes.',
                    $message === false ? '0' : strlen($message)
                )
            );
        }

        return unserialize(base64_decode($message));
    }

    private function canReadFromResource(): bool
    {
        set_error_handler(fn() => true);
        $read = [$this->resource];
        $write = $except = null;
        $canReadFromResource = stream_select($read, $write, $except, 120, null) === 1;
        restore_error_handler();

        return $canReadFromResource;
    }

    public function __destruct()
    {
        @fclose($this->resource);
    }
}
