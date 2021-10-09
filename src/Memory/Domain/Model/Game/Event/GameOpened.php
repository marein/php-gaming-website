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
    private GameId $gameId;

    private int $numberOfCards;

    private Player $player;

    private DateTimeImmutable $occurredOn;

    public function __construct(GameId $gameId, int $numberOfCards, Player $player)
    {
        $this->gameId = $gameId;
        $this->numberOfCards = $numberOfCards;
        $this->player = $player;
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
            'numberOfCards' => $this->numberOfCards,
            'playerId' => $this->player->id()
        ];
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function name(): string
    {
        return 'GameOpened';
    }
}
