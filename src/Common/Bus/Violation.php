<?php

declare(strict_types=1);

namespace Gaming\Common\Bus;

final class Violation
{
    /**
     * @param ViolationParameter[] $parameters
     */
    public function __construct(
        private readonly string $propertyPath,
        private readonly string $identifier,
        private readonly array $parameters
    ) {
    }

    public function propertyPath(): string
    {
        return $this->propertyPath;
    }

    public function identifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return ViolationParameter[]
     */
    public function parameters(): array
    {
        return $this->parameters;
    }
}
