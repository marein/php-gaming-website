<?php

declare(strict_types=1);

namespace Gaming\Memory\Domain\Model\Game\Event;

use Gaming\Common\Domain\DomainEvent;
use Gaming\Memory\Domain\Model\Game\GameId;
use Gaming\Memory\Domain\Model\Game\Player;
use Gaming\Memory\Domain\Model\Game\PlayerPool;

final class GameStarted implements DomainEvent
{
    private string $gameId;

    /**
     * @var string[]
     */
    private array $playerIds;

    public function __construct(GameId $gameId, PlayerPool $playerPool)
    {
        $this->gameId = $gameId->toString();
        $this->playerIds = array_map(
            static fn(Player $player): string => $player->id(),
            $playerPool->players()
        );
    }

    public function aggregateId(): string
    {
        return $this->gameId;
    }

    /**
     * @return string[]
     */
    public function playerIds(): array
    {
        return $this->playerIds;
    }
}
