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

    public function resignedPlayerId(): string
    {
        return $this->resignedPlayerId;
    }

    public function opponentPlayerId(): string
    {
        return $this->opponentPlayerId;
    }
}
