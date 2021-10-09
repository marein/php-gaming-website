<?php

declare(strict_types=1);

namespace Gaming\Common\Domain;

use DateTimeImmutable;

interface DomainEvent
{
    public function name(): string;

    public function occurredOn(): DateTimeImmutable;

    public function aggregateId(): string;

    /**
     * @return array<string, mixed> The values are scalars or nestable arrays of scalars.
     */
    public function payload(): array;
}
