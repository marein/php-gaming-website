<?php
declare(strict_types=1);

namespace Gaming\Common\Domain;

use DateTimeImmutable;

interface DomainEvent
{
    /**
     * Returns the name of the event.
     *
     * @return string
     */
    public function name(): string;

    /**
     * Returns the DateTimeImmutable when the event occurred.
     *
     * @return DateTimeImmutable
     */
    public function occurredOn(): DateTimeImmutable;

    /**
     * Returns the aggregate id of the event.
     *
     * @return string
     */
    public function aggregateId(): string;

    /**
     * Returns the payload. This array must only hold primitive types.
     *
     * @return array<string, mixed>
     */
    public function payload(): array;
}
