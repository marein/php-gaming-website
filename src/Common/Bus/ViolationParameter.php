<?php

declare(strict_types=1);

namespace Gaming\Common\Bus;

final class ViolationParameter
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var bool|int|float|string
     */
    private bool|int|float|string $value;

    /**
     * ViolationParameter constructor.
     *
     * @param string $name
     * @param bool|int|float|string $value
     */
    public function __construct(string $name, bool|int|float|string $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return bool|int|float|string
     */
    public function value(): bool|int|float|string
    {
        return $this->value;
    }
}
