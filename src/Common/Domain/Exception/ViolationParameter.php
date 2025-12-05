<?php

declare(strict_types=1);

namespace Gaming\Common\Domain\Exception;

final class ViolationParameter
{
    public function __construct(
        public readonly string $name,
        public readonly bool|int|float|string $value
    ) {
    }
}
