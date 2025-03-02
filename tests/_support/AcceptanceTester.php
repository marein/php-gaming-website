<?php

declare(strict_types=1);

use Codeception\Actor;
use Codeception\Lib\Actor\Shared\Friend;
use Codeception\Lib\Actor\Shared\Retry;
use Codeception\Scenario;

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
 *
 * @SuppressWarnings(PHPMD)
 */
class AcceptanceTester extends Actor
{
    use _generated\AcceptanceTesterActions;
    use Friend;
    use Retry;

    public function __construct(Scenario $scenario)
    {
        parent::__construct($scenario);

        $this->retry(3);
    }
}
