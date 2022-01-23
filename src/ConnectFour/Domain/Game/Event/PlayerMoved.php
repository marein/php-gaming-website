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
    private string $gameId;

    private int $x;

    private int $y;

    private int $color;

    private DateTimeImmutable $occurredOn;

    public function __construct(GameId $gameId, Point $point, Stone $stone)
    {
        $this->gameId = $gameId->toString();
        $this->x = $point->x();
        $this->y = $point->y();
        $this->color = $stone->color();
        $this->occurredOn = Clock::instance()->now();
    }

    public function aggregateId(): string
    {
        return $this->gameId;
    }

    public function x(): int
    {
        return $this->x;
    }

    public function y(): int
    {
        return $this->y;
    }

    public function color(): int
    {
        return $this->color;
    }

    public function payload(): array
    {
        return [
            'gameId' => $this->gameId,
            'x' => $this->x,
            'y' => $this->y,
            'color' => $this->color
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
