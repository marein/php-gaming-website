<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\State;

use Gaming\ConnectFour\Domain\Game\Board\Board;
use Gaming\ConnectFour\Domain\Game\Configuration;
use Gaming\ConnectFour\Domain\Game\Event\GameAborted;
use Gaming\ConnectFour\Domain\Game\Event\PlayerJoined;
use Gaming\ConnectFour\Domain\Game\Exception\GameNotRunningException;
use Gaming\ConnectFour\Domain\Game\Exception\PlayerNotOwnerException;
use Gaming\ConnectFour\Domain\Game\GameId;

final class Open implements State
{
    public function __construct(
        private readonly Configuration $configuration,
        private readonly string $playerId
    ) {
    }

    public function join(GameId $gameId, string $playerId): Transition
    {
        $size = $this->configuration->size();
        $width = $size->width();
        $height = $size->height();
        $players = $this->configuration->createPlayers($this->playerId, $playerId);

        return new Transition(
            new Running(
                $this->configuration->winningRules(),
                $width * $height,
                Board::empty($size),
                $players
            ),
            [
                new PlayerJoined(
                    $gameId,
                    $playerId,
                    $this->playerId,
                    $players->current()->id(),
                    $players->switch()->current()->id()
                )
            ]
        );
    }

    public function abort(GameId $gameId, string $playerId): Transition
    {
        if ($this->playerId !== $playerId) {
            throw new PlayerNotOwnerException();
        }

        return new Transition(
            new Aborted(),
            [
                new GameAborted(
                    $gameId,
                    $this->playerId
                )
            ]
        );
    }

    public function resign(GameId $gameId, string $playerId): Transition
    {
        throw new GameNotRunningException();
    }

    public function move(GameId $gameId, string $playerId, int $column): Transition
    {
        throw new GameNotRunningException();
    }
}
