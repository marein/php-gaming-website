<?php

declare(strict_types=1);

namespace Gaming\Memory\Domain\Model\Game\Event;

use Gaming\Common\Domain\DomainEvent;
use Gaming\Memory\Domain\Model\Game\GameId;
use Gaming\Memory\Domain\Model\Game\Player;
use Gaming\Memory\Domain\Model\Game\PlayerPool;

final class GameStarted implements DomainEvent
{
    private GameId $gameId;

    private PlayerPool $playerPool;

    public function __construct(GameId $gameId, PlayerPool $playerPool)
    {
        $this->gameId = $gameId;
        $this->playerPool = $playerPool;
    }

    public function aggregateId(): string
    {
        return $this->gameId->toString();
    }

    public function payload(): array
    {
        return [
            'gameId' => $this->gameId->toString(),
            'playerIds' => array_map(
                static fn(Player $player): string => $player->id(),
                $this->playerPool->players()
            )
        ];
    }

    public function name(): string
    {
        return 'GameStarted';
    }
}
