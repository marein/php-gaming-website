<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Event;

use DateTimeImmutable;
use Gaming\Common\Clock\Clock;
use Gaming\Common\Domain\DomainEvent;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\Player;

final class GameWon implements DomainEvent
{
    private string $gameId;

    private string $winnerPlayerId;

    private DateTimeImmutable $occurredOn;

    public function __construct(GameId $gameId, Player $winnerPlayer)
    {
        $this->gameId = $gameId->toString();
        $this->winnerPlayerId = $winnerPlayer->id();
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
            'winnerPlayerId' => $this->winnerPlayerId
        ];
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function name(): string
    {
        return 'GameWon';
    }
}
