<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Domain\Challenge\Exception;

use Gaming\Common\Domain\Exception\Violation;
use Gaming\Common\Domain\Exception\Violations;

final class CannotAcceptOwnChallengeException extends ChallengeException
{
    public function __construct()
    {
        parent::__construct(new Violations(new Violation('cannot_accept_own_challenge')));
    }
}
