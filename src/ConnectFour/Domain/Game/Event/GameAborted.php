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
    private string $gameId;

    private string $abortedPlayerId;

    private string $opponentPlayerId;

    private DateTimeImmutable $occurredOn;

    public function __construct(GameId $gameId, Player $abortedPlayer, Player $opponentPlayer = null)
    {
        $this->gameId = $gameId->toString();
        $this->abortedPlayerId = $abortedPlayer->id();
        $this->opponentPlayerId = $opponentPlayer ? $opponentPlayer->id() : '';
        $this->occurredOn = Clock::instance()->now();
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

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function name(): string
    {
        return 'GameAborted';
    }
}
