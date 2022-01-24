<?php

declare(strict_types=1);

namespace Gaming\Memory\Domain\Model\Game\Event;

use Gaming\Common\Domain\DomainEvent;
use Gaming\Memory\Domain\Model\Game\GameId;
use Gaming\Memory\Domain\Model\Game\Player;

final class PlayerJoined implements DomainEvent
{
    private GameId $gameId;

    private Player $player;

    public function __construct(GameId $gameId, Player $player)
    {
        $this->gameId = $gameId;
        $this->player = $player;
    }

    public function aggregateId(): string
    {
        return $this->gameId->toString();
    }

    public function payload(): array
    {
        return [
            'gameId' => $this->gameId->toString(),
            'playerId' => $this->player->id()
        ];
    }

    public function name(): string
    {
        return 'PlayerJoined';
    }
}
