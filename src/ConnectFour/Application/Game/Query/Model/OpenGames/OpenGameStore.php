<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\Model\OpenGames;

interface OpenGameStore
{
    /**
     * Store the open game.
     *
     * @param OpenGame $openGame
     */
    public function save(OpenGame $openGame): void;

    /**
     * Remove the game with the given id.
     *
     * @param string $gameId
     */
    public function remove(string $gameId): void;

    /**
     * Find all open games.
     *
     * @return OpenGames
     */
    public function all(): OpenGames;
}
