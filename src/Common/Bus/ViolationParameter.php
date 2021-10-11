<?php

declare(strict_types=1);

namespace Gaming\Common\Bus;

final class ViolationParameter
{
    private string $name;

    private bool|int|float|string $value;

    public function __construct(string $name, bool|int|float|string $value)
    {
        $this->name = $name;
        $this->value = $value;
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
