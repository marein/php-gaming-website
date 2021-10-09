<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Event;

use DateTimeImmutable;
use Gaming\Common\Clock\Clock;
use Gaming\Common\Domain\DomainEvent;
use Gaming\ConnectFour\Domain\Game\GameId;

final class GameDrawn implements DomainEvent
{
    private GameId $gameId;

    private DateTimeImmutable $occurredOn;

    public function __construct(GameId $gameId)
    {
        $this->gameId = $gameId;
        $this->occurredOn = Clock::instance()->now();
    }

    public function aggregateId(): string
    {
        return $this->gameId->toString();
    }

    public function payload(): array
    {
        return [
            'gameId' => $this->gameId->toString()
        ];
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function name(): string
    {
        return 'GameDrawn';
    }
}
