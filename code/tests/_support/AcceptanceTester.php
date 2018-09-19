<?php
declare(strict_types=1);

/**
 * Inherited Methods
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;

    /**
     * Wait for the current url.
     *
     * @param string $uri
     * @param int    $timeout
     */
    public function waitForCurrentUrl(string $uri, int $timeout): void
    {
        try {
            $this->seeInCurrentUrl($uri);
        } catch (\PHPUnit\Framework\ExpectationFailedException $exception) {
            $nextTimeout = $timeout - 1;

            if ($nextTimeout <= 0) {
                throw $exception;
            }

            $this->wait(1);
            $this->waitForCurrentUrl($uri, $nextTimeout);
        }
    }
}
