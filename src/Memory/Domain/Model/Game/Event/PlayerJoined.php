<?php

declare(strict_types=1);

namespace Gaming\Memory\Domain\Model\Game\Event;

use Gaming\Common\Domain\DomainEvent;
use Gaming\Memory\Domain\Model\Game\GameId;
use Gaming\Memory\Domain\Model\Game\Player;

final class PlayerJoined implements DomainEvent
{
    private string $gameId;

    private string $playerId;

    public function __construct(GameId $gameId, Player $player)
    {
        $this->gameId = $gameId->toString();
        $this->playerId = $player->id();
    }

    public function aggregateId(): string
    {
        return $this->gameId;
    }

    public function playerId(): string
    {
        return $this->playerId;
    }

    public function payload(): array
    {
        return [
            'gameId' => $this->gameId,
            'playerId' => $this->playerId
        ];
    }

    public function name(): string
    {
        return 'PlayerJoined';
    }
}
