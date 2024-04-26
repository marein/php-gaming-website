<?php

declare(strict_types=1);

namespace Gaming\Common\Bus;

use Exception;
use InvalidArgumentException;

final class RetryBus implements Bus
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        private readonly Bus $bus,
        private readonly int $numberOfRetries,
        private readonly string $retryOnException
    ) {
        if ($numberOfRetries < 1) {
            throw new InvalidArgumentException('Number of retries must be greater than 0.');
        }
    }

    public function handle(Request $request): mixed
    {
        return $this->handleOrThrow($request);
    }

    /**
     * @param Request<mixed> $request
     *
     * @throws Exception
     */
    private function handleOrThrow(Request $request, int $currentTry = 1): mixed
    {
        try {
            return $this->bus->handle($request);
        } catch (Exception $exception) {
            if ($exception instanceof $this->retryOnException && $currentTry < $this->numberOfRetries) {
                return $this->handleOrThrow($request, $currentTry + 1);
            }

            throw $exception;
        }
    }
}
