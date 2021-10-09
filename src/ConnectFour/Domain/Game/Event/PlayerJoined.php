<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Event;

use DateTimeImmutable;
use Gaming\Common\Clock\Clock;
use Gaming\Common\Domain\DomainEvent;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\Player;

final class PlayerJoined implements DomainEvent
{
    private GameId $gameId;

    private Player $joinedPlayer;

    private Player $opponentPlayer;

    private DateTimeImmutable $occurredOn;

    public function __construct(GameId $gameId, Player $joinedPlayer, Player $opponentPlayer)
    {
        $this->gameId = $gameId;
        $this->joinedPlayer = $joinedPlayer;
        $this->opponentPlayer = $opponentPlayer;
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
            'opponentPlayerId' => $this->opponentPlayer->id(),
            'joinedPlayerId' => $this->joinedPlayer->id()
        ];
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function name(): string
    {
        return 'PlayerJoined';
    }
}
