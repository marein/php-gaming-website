<?php
declare(strict_types=1);

namespace Gaming\Common\Application;

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
     * @var string
     */
    private $retryOnException;

    /**
     * RetryApplicationLifeCycle constructor.
     *
     * @param ApplicationLifeCycle $applicationLifeCycle
     * @param int                  $numberOfRetries
     * @param string               $retryOnException FQCN of the exception which trigger the retries.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        ApplicationLifeCycle $applicationLifeCycle,
        int $numberOfRetries,
        string $retryOnException
    ) {
        if ($numberOfRetries < 1) {
            throw new \InvalidArgumentException('Number of retries must be greater than 0.');
        }

        $this->applicationLifeCycle = $applicationLifeCycle;
        $this->numberOfRetries = $numberOfRetries;
        $this->retryOnException = $retryOnException;
    }

    /**
     * @inheritdoc
     */
    public function run(callable $action)
    {
        return $this->runOrThrow($action);
    }

    /**
     * Run the given action.
     * Retry if the configured exception occur and the number of retries isn't reached.
     *
     * @param callable $action
     * @param int      $currentTry
     *
     * @return mixed
     * @throws \Exception
     */
    private function runOrThrow(callable $action, int $currentTry = 1)
    {
        try {
            return $this->applicationLifeCycle->run($action);
        } catch (\Exception $exception) {
            if ($exception instanceof $this->retryOnException && $currentTry < $this->numberOfRetries) {
                return $this->runOrThrow($action, $currentTry + 1);
            }

            throw $exception;
        }
    }
}
