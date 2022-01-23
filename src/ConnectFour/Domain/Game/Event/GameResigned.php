<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Event;

use DateTimeImmutable;
use Gaming\Common\Clock\Clock;
use Gaming\Common\Domain\DomainEvent;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\Player;

final class GameResigned implements DomainEvent
{
    private string $gameId;

    private string $resignedPlayerId;

    private string $opponentPlayerId;

    private DateTimeImmutable $occurredOn;

    public function __construct(GameId $gameId, Player $resignedPlayer, Player $opponentPlayer)
    {
        $this->gameId = $gameId->toString();
        $this->resignedPlayerId = $resignedPlayer->id();
        $this->opponentPlayerId = $opponentPlayer->id();
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
            'resignedPlayerId' => $this->resignedPlayerId,
            'opponentPlayerId' => $this->opponentPlayerId
        ];
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function name(): string
    {
        return 'GameResigned';
    }
}
