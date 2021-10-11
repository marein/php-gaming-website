<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Event;

use DateTimeImmutable;
use Gaming\Common\Clock\Clock;
use Gaming\Common\Domain\DomainEvent;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\Player;

final class GameAborted implements DomainEvent
{
    private GameId $gameId;

    private Player $abortedPlayer;

    private ?Player $opponentPlayer;

    private DateTimeImmutable $occurredOn;

    public function __construct(GameId $gameId, Player $abortedPlayer, Player $opponentPlayer = null)
    {
        $this->gameId = $gameId;
        $this->abortedPlayer = $abortedPlayer;
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
            'abortedPlayerId' => $this->abortedPlayer->id(),
            'opponentPlayerId' => $this->opponentPlayer ? $this->opponentPlayer->id() : ''
        ];
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function name(): string
    {
        return 'GameAborted';
    }
}
