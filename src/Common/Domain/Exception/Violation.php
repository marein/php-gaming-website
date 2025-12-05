<?php

declare(strict_types=1);

namespace Gaming\Common\Domain\Exception;

final class Violation
{
    /**
     * @param ViolationParameter[] $parameters
     */
    public function __construct(
        public readonly string $identifier,
        public readonly array $parameters = [],
        public readonly string $propertyPath = ''
    ) {
    }
}
