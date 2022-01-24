<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Event;

use Gaming\Common\Domain\DomainEvent;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\Player;

final class PlayerJoined implements DomainEvent
{
    private string $gameId;

    private string $joinedPlayerId;

    private string $opponentPlayerId;

    public function __construct(GameId $gameId, Player $joinedPlayer, Player $opponentPlayer)
    {
        $this->gameId = $gameId->toString();
        $this->joinedPlayerId = $joinedPlayer->id();
        $this->opponentPlayerId = $opponentPlayer->id();
    }

    public function aggregateId(): string
    {
        return $this->gameId;
    }

    public function joinedPlayerId(): string
    {
        return $this->joinedPlayerId;
    }

    public function opponentPlayerId(): string
    {
        return $this->opponentPlayerId;
    }

    public function payload(): array
    {
        return [
            'gameId' => $this->gameId,
            'opponentPlayerId' => $this->opponentPlayerId,
            'joinedPlayerId' => $this->joinedPlayerId
        ];
    }

    public function name(): string
    {
        return 'PlayerJoined';
    }
}
