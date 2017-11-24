<?php

namespace Gambling\Common\Application;

final class RetryApplicationLifeCycle implements ApplicationLifeCycle
{
    /**
     * @var ApplicationLifeCycle
     */
    private $applicationLifeCycle;

    /**
     * @var int
     */
    private $numberOfRetries;

    /**
     * RetryApplicationLifeCycle constructor.
     *
     * @param ApplicationLifeCycle $applicationLifeCycle
     * @param int                  $numberOfRetries
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(ApplicationLifeCycle $applicationLifeCycle, int $numberOfRetries)
    {
        if ($numberOfRetries < 1) {
            throw new \InvalidArgumentException('Number of retries must be greater than 0.');
        }

        $this->numberOfRetries = $numberOfRetries;
        $this->applicationLifeCycle = $applicationLifeCycle;
    }

    /**
     * @inheritdoc
     */
    public function run(callable $action)
    {
        $currentNumberOfRetries = 0;
        $lastException = null;

        while ($currentNumberOfRetries < $this->numberOfRetries) {
            try {
                return $this->applicationLifeCycle->run($action);
            } catch (\Exception $exception) {
                $currentNumberOfRetries++;
                $lastException = $exception;
            }
        }

        throw $lastException;
    }
}
