<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\State;

use Gaming\Common\Domain\DomainEvent;

final class Transition
{
    /**
     * @var State
     */
    private State $state;

    /**
     * @var DomainEvent[]
     */
    private array $domainEvents;

    /**
     * Transition constructor.
     *
     * @param State         $state
     * @param DomainEvent[] $domainEvents
     */
    public function __construct(State $state, array $domainEvents = [])
    {
        $this->state = $state;
        $this->domainEvents = $domainEvents;
    }

    /**
     * @return State
     */
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
