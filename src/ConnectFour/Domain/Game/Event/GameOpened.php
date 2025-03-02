<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Event;

use Gaming\Common\Domain\DomainEvent;
use Gaming\ConnectFour\Domain\Game\Board\Size;
use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\GameId;

final class GameOpened implements DomainEvent
{
    private string $gameId;

    private int $width;

    private int $height;

    public readonly ?int $preferredStone;

    public function __construct(
        GameId $gameId,
        Size $size,
        ?Stone $preferredStone,
        public readonly string $playerId
    ) {
        $this->gameId = $gameId->toString();
        $this->width = $size->width();
        $this->height = $size->height();
        $this->preferredStone = $preferredStone?->value;
    }

    public function aggregateId(): string
    {
        return $this->gameId;
    }

    public function width(): int
    {
        return $this->width;
    }

    public function height(): int
    {
        return $this->height;
    }

    public function playerId(): string
    {
        return $this->playerId;
    }
}
