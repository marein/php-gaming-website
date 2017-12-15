<?php

namespace Gambling\ConnectFour\Domain\Game\Event;

use Gambling\Common\Clock\Clock;
use Gambling\Common\Domain\DomainEvent;
use Gambling\ConnectFour\Domain\Game\GameId;

final class GameDrawn implements DomainEvent
{
    /**
     * @var GameId
     */
    private $gameId;

    /**
     * @var \DateTimeImmutable
     */
    private $occurredOn;

    /**
     * GameDrawn constructor.
     *
     * @param GameId $gameId
     */
    public function __construct(GameId $gameId)
    {
        $this->gameId = $gameId;
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
            'gameId' => $this->gameId->toString()
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
        return 'GameDrawn';
    }
}
