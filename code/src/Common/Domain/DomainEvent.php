<?php

namespace Gambling\Common\Domain;

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
     * @return \DateTimeImmutable
     */
    public function occurredOn(): \DateTimeImmutable;

    /**
     * Returns the payload. This array must only hold primitive types.
     *
     * @return array
     */
    public function payload(): array;
}
