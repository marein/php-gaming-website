<?php

namespace Gambling\ConnectFour\Domain\Game\Event;

use Gambling\Common\Clock\Clock;
use Gambling\Common\Domain\DomainEvent;
use Gambling\ConnectFour\Domain\Game\GameId;
use Gambling\ConnectFour\Domain\Game\Player;

final class GameAborted implements DomainEvent
{
    /**
     * @var GameId
     */
    private $gameId;

    /**
     * @var Player
     */
    private $abortedPlayer;

    /**
     * @var Player|null
     */
    private $opponentPlayer;

    /**
     * @var \DateTimeImmutable
     */
    private $occurredOn;

    /**
     * GameAborted constructor.
     *
     * @param GameId      $gameId
     * @param Player      $abortedPlayer
     * @param Player|null $opponentPlayer
     */
    public function __construct(GameId $gameId, Player $abortedPlayer, Player $opponentPlayer = null)
    {
        $this->gameId = $gameId;
        $this->abortedPlayer = $abortedPlayer;
        $this->opponentPlayer = $opponentPlayer;
        $this->occurredOn = Clock::instance()->now();
    }

    /**
     * @inheritdoc
     */
    public function payload(): array
    {
        return [
            'gameId'           => $this->gameId->toString(),
            'abortedPlayerId'  => $this->abortedPlayer->id(),
            'opponentPlayerId' => $this->opponentPlayer ? $this->opponentPlayer->id() : ''
        ];
    }

    /**
     * @inheritdoc
     */
    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }

    /**
     * @inheritdoc
     */
    public function name(): string
    {
        return 'GameAborted';
    }
}
