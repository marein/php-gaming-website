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
    private string $gameId;

    private int $width;

    private int $height;

    private string $playerId;

    private DateTimeImmutable $occurredOn;

    public function __construct(GameId $gameId, Size $size, Player $player)
    {
        $this->gameId = $gameId->toString();
        $this->width = $size->width();
        $this->height = $size->height();
        $this->playerId = $player->id();
        $this->occurredOn = Clock::instance()->now();
    }

    public function aggregateId(): string
    {
        return $this->gameId;
    }

    public function payload(): array
    {
        return [
            'gameId' => $this->gameId,
            'width' => $this->width,
            'height' => $this->height,
            'playerId' => $this->playerId
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
