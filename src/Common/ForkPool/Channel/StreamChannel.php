<?php

declare(strict_types=1);

namespace Gaming\Common\ForkPool\Channel;

use Gaming\Common\ForkPool\Exception\ForkPoolException;

final class StreamChannel implements Channel
{
    /**
     * @param resource $resource
     *
     * @throws ForkPoolException
     */
    public function __construct(
        private $resource,
        int $sendReceiveTimeoutInSeconds
    ) {
        if (!stream_set_timeout($this->resource, $sendReceiveTimeoutInSeconds)) {
            throw new ForkPoolException(
                sprintf(
                    'Cannot set timeout to %s seconds.',
                    $sendReceiveTimeoutInSeconds
                )
            );
        }
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

    public function __destruct()
    {
        @fclose($this->resource);
    }
}
