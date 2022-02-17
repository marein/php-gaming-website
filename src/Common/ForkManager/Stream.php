<?php

declare(strict_types=1);

namespace Gaming\Common\ForkManager;

use Gaming\Common\ForkManager\Exception\ForkManagerException;

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
            throw new ForkManagerException(
                error_get_last()['message'] ?? 'Cannot read data.'
            );
        }

        return $data;
    }

    /**
     * @throws ForkManagerException
     */
    public function write(string $data): void
    {
        $data .= PHP_EOL;

        $numberOfWrittenBytes = @fwrite($this->resource, $data, strlen($data));
        if ($numberOfWrittenBytes === false) {
            throw new ForkManagerException(
                error_get_last()['message'] ?? 'Cannot write data.'
            );
        }
    }

    /**
     * @throws ForkManagerException
     */
    public function close(): void
    {
        if (!fclose($this->resource)) {
            throw new ForkManagerException(
                error_get_last()['message'] ?? 'Cannot close stream.'
            );
        }
    }
}
