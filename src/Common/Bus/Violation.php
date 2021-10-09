<?php

declare(strict_types=1);

namespace Gaming\Common\Bus;

final class Violation
{
    private string $propertyPath;

    private string $identifier;

    /**
     * @var ViolationParameter[]
     */
    private array $parameters;

    /**
     * @param ViolationParameter[] $parameters
     */
    public function __construct(string $propertyPath, string $identifier, array $parameters)
    {
        $this->propertyPath = $propertyPath;
        $this->identifier = $identifier;
        $this->parameters = $parameters;
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
