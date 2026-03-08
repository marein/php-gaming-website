<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Exception;

use Gaming\Common\Domain\Exception\Violation;
use Gaming\Common\Domain\Exception\ViolationParameter;
use Gaming\Common\Domain\Exception\Violations;

final class SizeTooSmallException extends InvalidSizeException
{
    public function __construct(int $width, int $height)
    {
        parent::__construct(
            new Violations(
                new Violation('invalid_size_too_small', [
                    new ViolationParameter('width', $width),
                    new ViolationParameter('height', $height)
                ])
            )
        );
    }
}
