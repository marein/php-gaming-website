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

    public function read(): string
    {
        $data = @stream_get_line($this->resource, 2048, PHP_EOL);
        if ($data === false) {
            throw new ForkControlException(
                error_get_last()['message'] ?? 'Cannot read data.'
            );
        }

        return $data;
    }

    /**
     * @throws ForkControlException
     */
    public function write(string $data): void
    {
        $data .= PHP_EOL;

        $numberOfWrittenBytes = @fwrite($this->resource, $data, strlen($data));
        if ($numberOfWrittenBytes === false) {
            throw new ForkControlException(
                error_get_last()['message'] ?? 'Cannot write data.'
            );
        }
    }

    /**
     * @throws ForkControlException
     */
    public function close(): void
    {
        if (!fclose($this->resource)) {
            throw new ForkControlException(
                error_get_last()['message'] ?? 'Cannot close stream.'
            );
        }
    }
}
