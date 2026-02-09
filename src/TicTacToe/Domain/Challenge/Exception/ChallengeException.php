<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Domain\Challenge\Exception;

use Gaming\Common\Domain\Exception\DomainException;
use Gaming\Common\Domain\Exception\Violation;
use Gaming\Common\Domain\Exception\Violations;

class ChallengeException extends DomainException
{
    public static function alreadyClosed(): self
    {
        return new self(new Violations(new Violation('challenge_already_closed')));
    }

    public static function cannotAcceptOwnChallenge(): self
    {
        return new self(new Violations(new Violation('cannot_accept_own_challenge')));
    }

    public static function notFound(): self
    {
        return new self(new Violations(new Violation('challenge_not_found')));
    }

    public static function onlyChallengerCanWithdraw(): self
    {
        return new self(new Violations(new Violation('only_challenger_can_withdraw')));
    }
}
