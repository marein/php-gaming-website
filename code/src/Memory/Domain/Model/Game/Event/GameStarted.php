<?php
declare(strict_types=1);

namespace Gaming\Memory\Domain\Model\Game\Event;

use Gaming\Common\Clock\Clock;
use Gaming\Common\Domain\DomainEvent;
use Gaming\Memory\Domain\Model\Game\GameId;

final class GameStarted implements DomainEvent
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
     * GameStarted constructor.
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
            'gameId' => $this->gameId->toString(),
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
        return 'GameStarted';
    }
}
