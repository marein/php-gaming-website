<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Event;

use Gaming\Common\Domain\DomainEvent;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\Player;

final class GameResigned implements DomainEvent
{
    private string $gameId;

    private string $resignedPlayerId;

    private string $opponentPlayerId;

    public function __construct(GameId $gameId, Player $resignedPlayer, Player $opponentPlayer)
    {
        $this->gameId = $gameId->toString();
        $this->resignedPlayerId = $resignedPlayer->id();
        $this->opponentPlayerId = $opponentPlayer->id();
    }

    public function aggregateId(): string
    {
        return $this->gameId;
    }

    public function payload(): array
    {
        return [
            'gameId' => $this->gameId,
            'resignedPlayerId' => $this->resignedPlayerId,
            'opponentPlayerId' => $this->opponentPlayerId
        ];
    }

    public function name(): string
    {
        return 'GameResigned';
    }
}
