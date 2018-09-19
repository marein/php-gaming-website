<?php
declare(strict_types=1);

namespace Gambling\ConnectFour\Domain\Game\State;

use Gambling\ConnectFour\Domain\Game\Board\Board;
use Gambling\ConnectFour\Domain\Game\Board\Stone;
use Gambling\ConnectFour\Domain\Game\Configuration;
use Gambling\ConnectFour\Domain\Game\Event\GameAborted;
use Gambling\ConnectFour\Domain\Game\Event\PlayerJoined;
use Gambling\ConnectFour\Domain\Game\Exception\GameNotRunningException;
use Gambling\ConnectFour\Domain\Game\Exception\PlayerNotOwnerException;
use Gambling\ConnectFour\Domain\Game\GameId;
use Gambling\ConnectFour\Domain\Game\Player;
use Gambling\ConnectFour\Domain\Game\Players;

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
    public function join(GameId $gameId, string $playerId): Transition
    {
        $joinedPlayer = new Player($playerId, Stone::yellow());
        $size = $this->configuration->size();
        $width = $size->width();
        $height = $size->height();

        return new Transition(
            new Running(
                $this->configuration->winningRule(),
                $width * $height,
                Board::empty($size),
                new Players($this->player, $joinedPlayer)
            ),
            [
                new PlayerJoined($gameId, $joinedPlayer, $this->player)
            ]
        );
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function resign(GameId $gameId, string $playerId): Transition
    {
        throw new GameNotRunningException();
    }

    /**
     * @inheritdoc
     */
    public function move(GameId $gameId, string $playerId, int $column): Transition
    {
        throw new GameNotRunningException();
    }
}
