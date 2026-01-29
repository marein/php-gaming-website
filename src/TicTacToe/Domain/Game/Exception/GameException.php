<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Domain\Game\Exception;

use Gaming\Common\Domain\Exception\DomainException;
use Gaming\Common\Domain\Exception\Violation;
use Gaming\Common\Domain\Exception\ViolationParameter;
use Gaming\Common\Domain\Exception\Violations;

class GameException extends DomainException
{
    public static function sizeOutOfRange(int $size, int $min, int $max): self
    {
        return new self(
            new Violations(
                new Violation('size_out_of_range', [
                    new ViolationParameter('min', $min),
                    new ViolationParameter('max', $max),
                    new ViolationParameter('value', $size)
                ])
            )
        );
    }
}
