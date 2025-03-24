<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\State;

use Gaming\ConnectFour\Domain\Game\Board\Board;
use Gaming\ConnectFour\Domain\Game\Event\GameAborted;
use Gaming\ConnectFour\Domain\Game\Event\GameDrawn;
use Gaming\ConnectFour\Domain\Game\Event\GameResigned;
use Gaming\ConnectFour\Domain\Game\Event\GameWon;
use Gaming\ConnectFour\Domain\Game\Event\PlayerMoved;
use Gaming\ConnectFour\Domain\Game\Exception\GameNotRunningException;
use Gaming\ConnectFour\Domain\Game\Exception\GameRunningException;
use Gaming\ConnectFour\Domain\Game\Exception\UnexpectedPlayerException;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\Players;
use Gaming\ConnectFour\Domain\Game\WinningRule\WinningRules;

final class Running implements State
{
    private WinningRules $winningRules;

    private int $numberOfMovesUntilDraw;

    private Board $board;

    private Players $players;

    public function __construct(
        WinningRules $winningRules,
        int $numberOfMovesUntilDraw,
        Board $board,
        Players $players
    ) {
        $this->winningRules = $winningRules;
        $this->numberOfMovesUntilDraw = $numberOfMovesUntilDraw;
        $this->board = $board;
        $this->players = $players;
    }

    public function move(GameId $gameId, string $playerId, int $column): Transition
    {
        $this->guardExpectedPlayer($playerId);

        $board = $this->board->dropStone($this->players->current()->stone(), $column);

        $domainEvents = [
            new PlayerMoved(
                $gameId,
                $board->lastUsedField()->point(),
                $board->lastUsedField()->stone(),
                $this->players->current()->id(),
                $this->players->switch()->current()->id()
            )
        ];

        $winningSequences = $this->winningRules->findWinningSequences($board);

        if (count($winningSequences) !== 0) {
            $domainEvents[] = new GameWon(
                $gameId,
                $this->players->current()->id(),
                $this->players->switch()->current()->id(),
                $winningSequences
            );

            return new Transition(
                new Won(),
                $domainEvents
            );
        }

        $numberOfMovesUntilDraw = $this->numberOfMovesUntilDraw - 1;

        if ($numberOfMovesUntilDraw === 0) {
            $domainEvents[] = new GameDrawn(
                $gameId,
                [$this->players->current()->id(), $this->players->switch()->current()->id()]
            );

            return new Transition(
                new Drawn(),
                $domainEvents
            );
        }

        return new Transition(
            new self(
                $this->winningRules,
                $numberOfMovesUntilDraw,
                $board,
                $this->players->switch()
            ),
            $domainEvents
        );
    }

    public function join(GameId $gameId, string $playerId): Transition
    {
        throw new GameRunningException();
    }

    public function abort(GameId $gameId, string $playerId): Transition
    {
        if (!$this->isAbortable()) {
            throw new GameRunningException();
        }

        return new Transition(
            new Aborted(),
            [
                new GameAborted(
                    $gameId,
                    $playerId,
                    $this->players->opponentOf($playerId)->id()
                )
            ]
        );
    }

    public function resign(GameId $gameId, string $playerId): Transition
    {
        if ($this->isAbortable()) {
            throw new GameNotRunningException();
        }

        return new Transition(
            new Resigned(),
            [
                new GameResigned(
                    $gameId,
                    $this->players->get($playerId),
                    $this->players->opponentOf($playerId)
                )
            ]
        );
    }

    /**
     * The game is only abortable until the second move is done.
     */
    private function isAbortable(): bool
    {
        $totalNumberOfMoves = $this->board->size()->height() * $this->board->size()->width();

        return $totalNumberOfMoves - $this->numberOfMovesUntilDraw < 2;
    }

    /**
     * @throws UnexpectedPlayerException
     */
    private function guardExpectedPlayer(string $playerId): void
    {
        if ($this->players->current()->id() !== $playerId) {
            throw new UnexpectedPlayerException();
        }
    }
}
