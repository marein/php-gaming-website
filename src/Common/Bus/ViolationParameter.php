<?php

declare(strict_types=1);

namespace Gaming\Common\Bus;

final class ViolationParameter
{
    public function __construct(
        private readonly string $name,
        private readonly bool|int|float|string $value
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function value(): bool|int|float|string
    {
        return $this->value;
    }
}
