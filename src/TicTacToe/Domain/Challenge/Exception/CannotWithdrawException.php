<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Domain\Challenge\Exception;

use Gaming\Common\Domain\Exception\Violation;
use Gaming\Common\Domain\Exception\Violations;

final class CannotWithdrawException extends ChallengeException
{
    public function __construct()
    {
        parent::__construct(new Violations(new Violation('only_challenger_can_withdraw')));
    }
}
