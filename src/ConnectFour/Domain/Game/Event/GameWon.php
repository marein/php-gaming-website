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
    private GameId $gameId;

    private Player $winnerPlayer;

    private DateTimeImmutable $occurredOn;

    public function __construct(GameId $gameId, Player $winnerPlayer)
    {
        $this->gameId = $gameId;
        $this->winnerPlayer = $winnerPlayer;
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
            'winnerPlayerId' => $this->winnerPlayer->id()
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
