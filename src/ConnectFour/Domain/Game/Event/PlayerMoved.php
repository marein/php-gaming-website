<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Event;

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

    public function __construct(
        GameId $gameId,
        Point $point,
        Stone $stone,
        public readonly string $playerId,
        public readonly string $nextPlayerId
    ) {
        $this->gameId = $gameId->toString();
        $this->x = $point->x();
        $this->y = $point->y();
        $this->color = $stone->value;
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
}
