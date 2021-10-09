<?php

declare(strict_types=1);

namespace Gaming\Common\Bus;

use Exception;
use InvalidArgumentException;

final class RetryBus implements Bus
{
    private Bus $bus;

    private int $numberOfRetries;

    private string $retryOnException;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        Bus $bus,
        int $numberOfRetries,
        string $retryOnException
    ) {
        if ($numberOfRetries < 1) {
            throw new InvalidArgumentException('Number of retries must be greater than 0.');
        }

        $this->bus = $bus;
        $this->numberOfRetries = $numberOfRetries;
        $this->retryOnException = $retryOnException;
    }

    public function handle(object $message): mixed
    {
        return $this->handleOrThrow($message);
    }

    /**
     * @throws Exception
     */
    private function handleOrThrow(object $message, int $currentTry = 1): mixed
    {
        try {
            return $this->bus->handle($message);
        } catch (Exception $exception) {
            if ($exception instanceof $this->retryOnException && $currentTry < $this->numberOfRetries) {
                return $this->handleOrThrow($message, $currentTry + 1);
            }

            throw $exception;
        }
    }
}
