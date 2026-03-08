<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Exception;

use Gaming\Common\Domain\Exception\Violation;
use Gaming\Common\Domain\Exception\ViolationParameter;
use Gaming\Common\Domain\Exception\Violations;

final class WinningSequenceLengthTooShortException extends GameException
{
    public function __construct(int $min, int $value)
    {
        parent::__construct(
            new Violations(
                new Violation('winning_sequence_length_too_short', [
                    new ViolationParameter('min', $min),
                    new ViolationParameter('value', $value)
                ])
            )
        );
    }
}
