<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Event;

use DateTimeImmutable;
use Gaming\Common\Clock\Clock;
use Gaming\Common\Domain\DomainEvent;
use Gaming\ConnectFour\Domain\Game\Board\Size;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\Player;

final class GameOpened implements DomainEvent
{
    private GameId $gameId;

    private Size $size;

    private Player $player;

    private DateTimeImmutable $occurredOn;

    public function __construct(GameId $gameId, Size $size, Player $player)
    {
        $this->gameId = $gameId;
        $this->size = $size;
        $this->player = $player;
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
            'width' => $this->size->width(),
            'height' => $this->size->height(),
            'playerId' => $this->player->id()
        ];
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function name(): string
    {
        return 'GameOpened';
    }
}
