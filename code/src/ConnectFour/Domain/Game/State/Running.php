<?php

namespace Gambling\ConnectFour\Domain\Game\State;

use Gambling\ConnectFour\Domain\Game\Board;
use Gambling\ConnectFour\Domain\Game\Event\GameAborted;
use Gambling\ConnectFour\Domain\Game\Event\GameDrawn;
use Gambling\ConnectFour\Domain\Game\Event\GameWon;
use Gambling\ConnectFour\Domain\Game\Event\PlayerMoved;
use Gambling\ConnectFour\Domain\Game\Exception\GameRunningException;
use Gambling\ConnectFour\Domain\Game\Exception\PlayerNotOwnerException;
use Gambling\ConnectFour\Domain\Game\Exception\UnexpectedPlayerException;
use Gambling\ConnectFour\Domain\Game\Game;
use Gambling\ConnectFour\Domain\Game\Player;
use Gambling\ConnectFour\Domain\Game\WinningRule\WinningRule;

final class Running implements State
{
    /**
     * @var WinningRule
     */
    private $winningRule;

    /**
     * @var int
     */
    private $numberOfMovesUntilDraw;

    /**
     * @var Board
     */
    private $board;

    /**
     * @var Player[]
     */
    private $players;

    /**
     * Game constructor.
     *
     * @param WinningRule $winningRule
     * @param int         $numberOfMovesUntilDraw
     * @param Board       $board
     * @param Player[]    $players
     */
    public function __construct(
        WinningRule $winningRule,
        int $numberOfMovesUntilDraw,
        Board $board,
        array $players
    ) {
        $this->winningRule = $winningRule;
        $this->numberOfMovesUntilDraw = $numberOfMovesUntilDraw;
        $this->board = $board;
        $this->players = $players;
    }

    /*************************************************************
     *                        Behaviour
     *************************************************************/

    /**
     * @inheritdoc
     */
    public function move(Game $game, string $playerId, int $column): void
    {
        $this->guardExpectedPlayer($playerId);

        $gameId = $game->id();
        $currentPlayer = $this->currentPlayer();
        $board = $this->board->dropStone($currentPlayer->stone(), $column);
        $numberOfMovesUntilDraw = $this->numberOfMovesUntilDraw - 1;
        $isWin = $this->winningRule->calculate($board);
        $switchedPlayers = $this->switchPlayer();
        $lastUsedField = $board->lastUsedField();

        $game->domainEvents[] = new PlayerMoved(
            $game->id(),
            $lastUsedField->point(),
            $lastUsedField->stone()
        );

        if ($isWin) {
            $game->state = new Won();
            $game->domainEvents[] = new GameWon($gameId, $currentPlayer);
        } elseif ($numberOfMovesUntilDraw == 0) {
            $game->state = new Drawn();
            $game->domainEvents[] = new GameDrawn($gameId);
        } else {
            $game->state = new self(
                $this->winningRule,
                $numberOfMovesUntilDraw,
                $board,
                $switchedPlayers
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function join(Game $game, string $playerId): void
    {
        throw new GameRunningException();
    }

    /**
     * @inheritdoc
     */
    public function abort(Game $game, string $playerId): void
    {
        $abortedPlayer = $this->playerWithId($playerId);

        // Only players of the game can abort the game.
        if ($abortedPlayer === null) {
            throw new PlayerNotOwnerException();
        }

        $size = $this->board->size();
        $height = $size->height();
        $width = $size->width();
        $totalNumberOfMoves = $height * $width;

        // The game is only abortable until the second move is done.
        if ($totalNumberOfMoves - $this->numberOfMovesUntilDraw > 1) {
            throw new GameRunningException();
        }

        $game->state = new Aborted();
        $game->domainEvents[] = new GameAborted(
            $game->id(),
            $abortedPlayer,
            $this->opponentOf($playerId)
        );
    }

    /**
     * Return the [Player]s with switched position.
     */
    private function switchPlayer(): array
    {
        return array_reverse($this->players);
    }

    /*************************************************************
     *                          Guards
     *************************************************************/

    /**
     * Guard if the given player id is the expected one.
     *
     * @param string $playerId
     *
     * @throws UnexpectedPlayerException
     */
    private function guardExpectedPlayer(string $playerId): void
    {
        if ($this->currentPlayer()->id() !== $playerId) {
            throw new UnexpectedPlayerException();
        }
    }

    /*************************************************************
     *                          Getter
     *************************************************************/

    /**
     * Returns the [Player].
     *
     * @return Player
     */
    private function currentPlayer(): Player
    {
        return $this->players[0];
    }

    /**
     * Returns the [Player] with the given id.
     *
     * @param string $playerId
     *
     * @return Player|null
     */
    private function playerWithId(string $playerId): ?Player
    {
        foreach ($this->players as $player) {
            if ($player->id() === $playerId) {
                return $player;
            }
        }

        return null;
    }

    /**
     * Returns the opponent of the [Player] with the given id.
     *
     * @return Player
     */
    private function opponentOf(string $playerId): Player
    {
        foreach ($this->players as $player) {
            if ($player->id() !== $playerId) {
                return $player;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function board(): Board
    {
        return $this->board;
    }
}
