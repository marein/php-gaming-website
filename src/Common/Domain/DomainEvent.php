<?php

declare(strict_types=1);

namespace Gaming\Common\Domain;

use DateTimeImmutable;

interface DomainEvent
{
    /**
     * @deprecated Will either be replaced by visitor or instance checks.
     */
    public function name(): string;

    public function occurredOn(): DateTimeImmutable;

    public function aggregateId(): string;

    /**
     * @deprecated Use properties/methods of the concrete event instead.
     *
     * @return array<string, mixed> The values are scalars or nestable arrays of scalars.
     */
    public function payload(): array;
}
