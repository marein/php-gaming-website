<?php

namespace Gambling\Common\Bus;

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
     * RetryBus constructor.
     *
     * @param Bus $bus
     * @param int $numberOfRetries
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(Bus $bus, $numberOfRetries)
    {
        if ($numberOfRetries < 1) {
            throw new \InvalidArgumentException('Number of retries must be greater than 0.');
        }

        $this->bus = $bus;
        $this->numberOfRetries = $numberOfRetries;
    }

    /**
     * @inheritdoc
     */
    public function handle($command)
    {
        $currentNumberOfRetries = 0;
        $lastException = null;

        while ($currentNumberOfRetries < $this->numberOfRetries) {
            try {
                return $this->bus->handle($command);
            } catch (\Exception $exception) {
                $currentNumberOfRetries++;
                $lastException = $exception;
            }
        }

        throw $lastException;
    }
}
