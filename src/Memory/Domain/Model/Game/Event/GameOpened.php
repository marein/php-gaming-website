<?php

declare(strict_types=1);

namespace Gaming\Memory\Domain\Model\Game\Event;

use DateTimeImmutable;
use Gaming\Common\Clock\Clock;
use Gaming\Common\Domain\DomainEvent;
use Gaming\Memory\Domain\Model\Game\GameId;
use Gaming\Memory\Domain\Model\Game\Player;

final class GameOpened implements DomainEvent
{
    /**
     * @var GameId
     */
    private GameId $gameId;

    /**
     * @var int
     */
    private int $numberOfCards;

    /**
     * @var Player
     */
    private Player $player;

    /**
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $occurredOn;

    /**
     * GameOpened constructor.
     *
     * @param GameId $gameId
     * @param int $numberOfCards
     * @param Player $player
     */
    public function __construct(GameId $gameId, int $numberOfCards, Player $player)
    {
        $this->gameId = $gameId;
        $this->numberOfCards = $numberOfCards;
        $this->player = $player;
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
            'numberOfCards' => $this->numberOfCards,
            'playerId' => $this->player->id()
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
        return 'GameOpened';
    }
}
