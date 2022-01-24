<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Event;

use Gaming\Common\Domain\DomainEvent;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\Player;

final class GameAborted implements DomainEvent
{
    private string $gameId;

    private string $abortedPlayerId;

    private string $opponentPlayerId;

    public function __construct(GameId $gameId, Player $abortedPlayer, Player $opponentPlayer = null)
    {
        $this->gameId = $gameId->toString();
        $this->abortedPlayerId = $abortedPlayer->id();
        $this->opponentPlayerId = $opponentPlayer ? $opponentPlayer->id() : '';
    }

    public function aggregateId(): string
    {
        return $this->gameId;
    }

    public function abortedPlayerId(): string
    {
        return $this->abortedPlayerId;
    }

    public function opponentPlayerId(): string
    {
        return $this->opponentPlayerId;
    }

    public function payload(): array
    {
        return [
            'gameId' => $this->gameId,
            'abortedPlayerId' => $this->abortedPlayerId,
            'opponentPlayerId' => $this->opponentPlayerId
        ];
    }

    public function name(): string
    {
        return 'GameAborted';
    }
}
