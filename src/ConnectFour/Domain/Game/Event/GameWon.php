<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Event;

use Gaming\Common\Domain\DomainEvent;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\Player;

final class GameWon implements DomainEvent
{
    private string $gameId;

    private string $winnerPlayerId;

    public function __construct(GameId $gameId, Player $winnerPlayer)
    {
        $this->gameId = $gameId->toString();
        $this->winnerPlayerId = $winnerPlayer->id();
    }

    public function aggregateId(): string
    {
        return $this->gameId;
    }

    public function payload(): array
    {
        return [
            'gameId' => $this->gameId,
            'winnerPlayerId' => $this->winnerPlayerId
        ];
    }

    public function name(): string
    {
        return 'GameWon';
    }
}
