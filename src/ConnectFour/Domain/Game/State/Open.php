<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\State;

use Gaming\ConnectFour\Domain\Game\Board\Board;
use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\Configuration;
use Gaming\ConnectFour\Domain\Game\Event\GameAborted;
use Gaming\ConnectFour\Domain\Game\Event\PlayerJoined;
use Gaming\ConnectFour\Domain\Game\Exception\GameNotRunningException;
use Gaming\ConnectFour\Domain\Game\Exception\PlayerNotOwnerException;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\Player;
use Gaming\ConnectFour\Domain\Game\Players;

final class Open implements State
{
    private Configuration $configuration;

    private Player $player;

    public function __construct(Configuration $configuration, Player $player)
    {
        $this->configuration = $configuration;
        $this->player = $player;
    }

    public function join(GameId $gameId, string $playerId): Transition
    {
        $joinedPlayer = new Player($playerId, Stone::Yellow);
        $size = $this->configuration->size();
        $width = $size->width();
        $height = $size->height();

        return new Transition(
            new Running(
                $this->configuration->winningRules(),
                $width * $height,
                Board::empty($size),
                new Players($this->player, $joinedPlayer)
            ),
            [
                new PlayerJoined($gameId, $joinedPlayer, $this->player)
            ]
        );
    }

    public function abort(GameId $gameId, string $playerId): Transition
    {
        if ($this->player->id() !== $playerId) {
            throw new PlayerNotOwnerException();
        }

        return new Transition(
            new Aborted(),
            [
                new GameAborted(
                    $gameId,
                    $this->player
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
