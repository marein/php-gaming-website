<?php

namespace Gambling\ConnectFour\Domain\Game\State;

use Gambling\ConnectFour\Domain\Game\Board\Board;
use Gambling\ConnectFour\Domain\Game\Event\GameAborted;
use Gambling\ConnectFour\Domain\Game\Event\GameDrawn;
use Gambling\ConnectFour\Domain\Game\Event\GameWon;
use Gambling\ConnectFour\Domain\Game\Event\PlayerMoved;
use Gambling\ConnectFour\Domain\Game\Exception\GameRunningException;
use Gambling\ConnectFour\Domain\Game\Exception\PlayerNotOwnerException;
use Gambling\ConnectFour\Domain\Game\Exception\UnexpectedPlayerException;
use Gambling\ConnectFour\Domain\Game\GameId;
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
     * Running constructor.
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
    public function move(GameId $gameId, string $playerId, int $column): Transition
    {
        $this->guardExpectedPlayer($playerId);

        $board = $this->board->dropStone($this->currentPlayer()->stone(), $column);

        $domainEvents = [
            new PlayerMoved(
                $gameId,
                $board->lastUsedField()->point(),
                $board->lastUsedField()->stone()
            )
        ];

        $isWin = $this->winningRule->calculate($board);
        $numberOfMovesUntilDraw = $this->numberOfMovesUntilDraw - 1;

        if ($isWin) {
            $domainEvents[] = new GameWon($gameId, $this->currentPlayer());

            return new Transition(
                new Won(),
                $domainEvents
            );
        } elseif ($numberOfMovesUntilDraw == 0) {
            $domainEvents[] = new GameDrawn($gameId);

            return new Transition(
                new Drawn(),
                $domainEvents
            );
        }

        return new Transition(
            new self(
                $this->winningRule,
                $numberOfMovesUntilDraw,
                $board,
                $this->switchPlayer()
            ),
            $domainEvents
        );
    }

    /**
     * @inheritdoc
     */
    public function join(GameId $gameId, string $playerId): Transition
    {
        throw new GameRunningException();
    }

    /**
     * @inheritdoc
     */
    public function abort(GameId $gameId, string $playerId): Transition
    {
        $abortedPlayer = $this->playerWithId($playerId);

        // Only players of the game can abort the game.
        if ($abortedPlayer === null) {
            throw new PlayerNotOwnerException();
        }

        $totalNumberOfMoves = $this->board->size()->height() * $this->board->size()->width();

        // The game is only abortable until the second move is done.
        if ($totalNumberOfMoves - $this->numberOfMovesUntilDraw > 1) {
            throw new GameRunningException();
        }

        return new Transition(
            new Aborted(),
            [
                new GameAborted(
                    $gameId,
                    $abortedPlayer,
                    $this->opponentOf($playerId)
                )
            ]
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
}
