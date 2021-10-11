<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\State;

use Gaming\Common\Domain\DomainEvent;

final class Transition
{
    private State $state;

    /**
     * @var DomainEvent[]
     */
    private array $domainEvents;

    /**
     * @param DomainEvent[] $domainEvents
     */
    public function __construct(State $state, array $domainEvents = [])
    {
        $this->state = $state;
        $this->domainEvents = $domainEvents;
    }

    public function state(): State
    {
        return $this->state;
    }

    /**
     * @return DomainEvent[]
     */
    public function domainEvents(): array
    {
        return $this->domainEvents;
    }
}
