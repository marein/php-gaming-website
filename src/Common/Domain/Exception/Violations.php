<?php

declare(strict_types=1);

namespace Gaming\Common\Domain\Exception;

use ArrayObject;
use Closure;
use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<Violation>
 */
final class Violations implements IteratorAggregate
{
    /**
     * @var Violation[] $violations
     */
    private array $violations;

    public function __construct(Violation ...$violations)
    {
        $this->violations = $violations;
    }

    public function first(): ?Violation
    {
        return $this->violations[0] ?? null;
    }

    public function count(): int
    {
        return count($this->violations);
    }

    /**
     * @param Closure(Violation): mixed $map
     *
     * @return mixed[]
     */
    public function map(Closure $map): array
    {
        return array_map($map, $this->violations);
    }

    /**
     * @return Traversable<Violation>
     */
    public function getIterator(): Traversable
    {
        return new ArrayObject($this->violations);
    }
}
