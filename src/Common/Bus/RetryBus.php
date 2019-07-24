<?php
declare(strict_types=1);

namespace Gaming\Common\Bus;

use Exception;
use InvalidArgumentException;

final class RetryBus implements Bus
{
    /**
     * @var Bus
     */
    private $bus;

    /**
     * @var int
     */
    private $numberOfRetries;

    /**
     * @var string
     */
    private $retryOnException;

    /**
     * RetryBus constructor.
     *
     * @param Bus    $bus
     * @param int    $numberOfRetries
     * @param string $retryOnException FQCN of the exception which trigger the retries.
     *
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

    /**
     * @inheritdoc
     */
    public function handle(object $message)
    {
        return $this->handleOrThrow($message);
    }

    /**
     * Handle the given message.
     * Retry if the configured exception occur and the number of retries isn't reached.
     *
     * @param object $message
     * @param int    $currentTry
     *
     * @return mixed
     * @throws Exception
     */
    private function handleOrThrow(object $message, int $currentTry = 1)
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
