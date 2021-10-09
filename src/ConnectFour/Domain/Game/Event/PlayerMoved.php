<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Event;

use DateTimeImmutable;
use Gaming\Common\Clock\Clock;
use Gaming\Common\Domain\DomainEvent;
use Gaming\ConnectFour\Domain\Game\Board\Point;
use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\GameId;

final class PlayerMoved implements DomainEvent
{
    private GameId $gameId;

    private Point $point;

    private Stone $stone;

    private DateTimeImmutable $occurredOn;

    public function __construct(GameId $gameId, Point $point, Stone $stone)
    {
        $this->gameId = $gameId;
        $this->point = $point;
        $this->stone = $stone;
        $this->occurredOn = Clock::instance()->now();
    }

    public function aggregateId(): string
    {
        return $this->gameId->toString();
    }

    public function payload(): array
    {
        return [
            'gameId' => $this->gameId->toString(),
            'x' => $this->point->x(),
            'y' => $this->point->y(),
            'color' => $this->stone->color()
        ];
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function name(): string
    {
        return 'PlayerMoved';
    }
}
