<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Event;

use Gaming\Common\Domain\DomainEvent;
use Gaming\ConnectFour\Domain\Game\GameId;

final class GameDrawn implements DomainEvent
{
    private string $gameId;

    public function __construct(GameId $gameId)
    {
        $this->gameId = $gameId->toString();
    }

    public function aggregateId(): string
    {
        return $this->gameId;
    }
}
