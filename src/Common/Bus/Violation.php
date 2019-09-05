<?php
declare(strict_types=1);

namespace Gaming\Common\Bus;

final class Violation
{
    /**
     * @var string
     */
    private $propertyPath;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var ViolationParameter[]
     */
    private $parameters;

    /**
     * Violation constructor.
     *
     * @param string               $propertyPath
     * @param string               $identifier
     * @param ViolationParameter[] $parameters
     */
    public function __construct(string $propertyPath, string $identifier, array $parameters)
    {
        $this->propertyPath = $propertyPath;
        $this->identifier = $identifier;
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function propertyPath(): string
    {
        return $this->propertyPath;
    }

    /**
     * @return string
     */
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
