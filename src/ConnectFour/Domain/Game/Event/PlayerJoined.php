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
    /**
     * @var GameId
     */
    private $gameId;

    /**
     * @var Player
     */
    private $joinedPlayer;

    /**
     * @var Player
     */
    private $opponentPlayer;

    /**
     * @var DateTimeImmutable
     */
    private $occurredOn;

    /**
     * PlayerJoined constructor.
     *
     * @param GameId $gameId
     * @param Player $joinedPlayer
     * @param Player $opponentPlayer
     */
    public function __construct(GameId $gameId, Player $joinedPlayer, Player $opponentPlayer)
    {
        $this->gameId = $gameId;
        $this->joinedPlayer = $joinedPlayer;
        $this->opponentPlayer = $opponentPlayer;
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
            'gameId'           => $this->gameId->toString(),
            'opponentPlayerId' => $this->opponentPlayer->id(),
            'joinedPlayerId'   => $this->joinedPlayer->id()
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
        return 'PlayerJoined';
    }
}
