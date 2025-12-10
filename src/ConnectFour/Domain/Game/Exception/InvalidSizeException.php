<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Exception;

use Gaming\Common\Domain\Exception\Violation;
use Gaming\Common\Domain\Exception\ViolationParameter;
use Gaming\Common\Domain\Exception\Violations;

final class InvalidSizeException extends GameException
{
    public function __construct(string $identifier, int $width = 0, int $height = 0)
    {
        parent::__construct(
            new Violations(
                new Violation($identifier, [
                    new ViolationParameter('width', $width),
                    new ViolationParameter('height', $height)
                ])
            )
        );
    }

    public static function productNotEven(int $width, int $height): self
    {
        return new self('invalid_size.not_even', $width, $height);
    }

    public static function tooSmall(int $width, int $height): self
    {
        return new self('invalid_size.too_small', $width, $height);
    }
}
