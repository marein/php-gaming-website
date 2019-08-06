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
     * @var mixed[]
     */
    private $context;

    /**
     * Violation constructor.
     *
     * @param string  $propertyPath
     * @param string  $identifier
     * @param mixed[] $context
     */
    public function __construct(string $propertyPath, string $identifier, array $context)
    {
        $this->propertyPath = $propertyPath;
        $this->identifier = $identifier;
        $this->context = $context;
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
     * @return mixed[]
     */
    public function context(): array
    {
        return $this->context;
    }
}
