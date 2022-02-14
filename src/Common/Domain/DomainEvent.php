<?php

declare(strict_types=1);

namespace Gaming\Common\Domain;

interface DomainEvent
{
    public function aggregateId(): string;
}
