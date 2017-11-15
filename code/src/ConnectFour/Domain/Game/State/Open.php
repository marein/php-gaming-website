<?php

namespace Gambling\ConnectFour\Domain\Game\State;

use Gambling\ConnectFour\Domain\Game\Board;
use Gambling\ConnectFour\Domain\Game\Configuration;
use Gambling\ConnectFour\Domain\Game\Event\GameAborted;
use Gambling\ConnectFour\Domain\Game\Event\PlayerJoined;
use Gambling\ConnectFour\Domain\Game\Exception\GameNotRunningException;
use Gambling\ConnectFour\Domain\Game\Exception\PlayerNotOwnerException;
use Gambling\ConnectFour\Domain\Game\Exception\PlayersNotUniqueException;
use Gambling\ConnectFour\Domain\Game\Game;
use Gambling\ConnectFour\Domain\Game\Player;
use Gambling\ConnectFour\Domain\Game\Stone;

final class Open implements State
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var Player
     */
    private $player;

    /**
     * Open constructor.
     *
     * @param Configuration $configuration
     * @param Player        $player
     */
    public function __construct(Configuration $configuration, Player $player)
    {
        $this->configuration = $configuration;
        $this->player = $player;
    }

    /**
     * @inheritdoc
     */
    public function join(Game $game, string $playerId): void
    {
        if ($this->player->id() === $playerId) {
            throw new PlayersNotUniqueException();
        }

        $joinedPlayer = new Player($playerId, Stone::yellow());
        $gameId = $game->id();
        $size = $this->configuration->size();
        $width = $size->width();
        $height = $size->height();

        $game->state = new Running(
            $this->configuration->winningRule(),
            $width * $height,
            Board::empty($size),
            [$this->player, $joinedPlayer]
        );
        $game->domainEvents[] = new PlayerJoined($gameId, $joinedPlayer, $this->player);
    }

    /**
     * @inheritdoc
     */
    public function abort(Game $game, string $playerId): void
    {
        if ($this->player->id() !== $playerId) {
            throw new PlayerNotOwnerException();
        }

        $gameId = $game->id();

        $game->state = new Aborted();
        $game->domainEvents[] = new GameAborted(
            $gameId,
            $this->player
        );
    }

    /**
     * @inheritdoc
     */
    public function move(Game $game, string $playerId, int $column): void
    {
        throw new GameNotRunningException();
    }

    /**
     * @inheritdoc
     */
    public function board(): Board
    {
        return Board::empty($this->configuration->size());
    }
}
