<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Domain\Game\Exception;

use Gaming\Common\Domain\Exception\Violation;
use Gaming\Common\Domain\Exception\ViolationParameter;
use Gaming\Common\Domain\Exception\Violations;

final class SizeOutOfRangeException extends GameException
{
    public function __construct(int $size, int $min, int $max)
    {
        parent::__construct(
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
