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
    /**
     * @var GameId
     */
    private GameId $gameId;

    /**
     * @var Player
     */
    private Player $winnerPlayer;

    /**
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $occurredOn;

    /**
     * GameWon constructor.
     *
     * @param GameId $gameId
     * @param Player $winnerPlayer
     */
    public function __construct(GameId $gameId, Player $winnerPlayer)
    {
        $this->gameId = $gameId;
        $this->winnerPlayer = $winnerPlayer;
        $this->occurredOn = Clock::instance()->now();
    }

    /**
     * @inheritdoc
     */
    public function aggregateId(): string
    {
        return $this->gameId->toString();
    }

    /**
     * @inheritdoc
     */
    public function payload(): array
    {
        return [
            'gameId' => $this->gameId->toString(),
            'winnerPlayerId' => $this->winnerPlayer->id()
        ];
    }

    /**
     * @inheritdoc
     */
    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    /**
     * @inheritdoc
     */
    public function name(): string
    {
        return 'GameWon';
    }
}
