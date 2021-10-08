<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Command;

final class MoveCommand
{
    /**
     * @var string
     */
    private string $gameId;

    /**
     * @var string
     */
    private string $playerId;

    /**
     * @var int
     */
    private int $column;

    /**
     * MoveCommand constructor.
     *
     * @param string $gameId
     * @param string $playerId
     * @param int $column
     */
    public function __construct(string $gameId, string $playerId, int $column)
    {
        $this->gameId = $gameId;
        $this->playerId = $playerId;
        $this->column = $column;
    }

    /**
     * @return string
     */
    public function gameId(): string
    {
        return $this->gameId;
    }

    /**
     * @return string
     */
    public function playerId(): string
    {
        return $this->playerId;
    }

    /**
     * @return int
     */
    public function column(): int
    {
        return $this->column;
    }
}
