<?php

declare(strict_types=1);

namespace Gaming\Memory\Domain\Model\Game\Event;

use Gaming\Common\Domain\DomainEvent;
use Gaming\Memory\Domain\Model\Game\GameId;

final class GameClosed implements DomainEvent
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
