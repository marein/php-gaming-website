<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Exception;

use Gaming\Common\Domain\Exception\Violation;
use Gaming\Common\Domain\Exception\ViolationParameter;
use Gaming\Common\Domain\Exception\Violations;

final class SizeProductNotEvenException extends InvalidSizeException
{
    public function __construct(int $width, int $height)
    {
        parent::__construct(
            new Violations(
                new Violation('invalid_size_not_even', [
                    new ViolationParameter('width', $width),
                    new ViolationParameter('height', $height)
                ])
            )
        );
    }
}
