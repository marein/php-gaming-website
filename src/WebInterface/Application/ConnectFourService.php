<?php
declare(strict_types=1);

namespace Gaming\WebInterface\Application;

interface ConnectFourService
{
    /**
     * List the open games.
     *
     * @return array<string, mixed>
     */
    public function openGames(): array;

    /**
     * List the running games.
     *
     * @return array<string, mixed>
     */
    public function runningGames(): array;

    /**
     * List games by specified player.
     *
     * @param string $playerId
     *
     * @return array<string, mixed>
     */
    public function gamesByPlayer(string $playerId): array;

    /**
     * Returns the game.
     *
     * @param string $gameId
     *
     * @return array<string, mixed>
     */
    public function game(string $gameId): array;

    /**
     * Open a new game.
     *
     * @param string $playerId
     *
     * @return array<string, mixed>
     */
    public function open(string $playerId): array;

    /**
     * Join a game.
     *
     * @param string $gameId
     * @param string $playerId
     *
     * @return array<string, mixed>
     */
    public function join(string $gameId, string $playerId): array;

    /**
     * Abort a game.
     *
     * @param string $gameId
     * @param string $playerId
     *
     * @return array<string, mixed>
     */
    public function abort(string $gameId, string $playerId): array;

    /**
     * Resign a game.
     *
     * @param string $gameId
     * @param string $playerId
     *
     * @return array<string, mixed>
     */
    public function resign(string $gameId, string $playerId): array;

    /**
     * Perform a move.
     *
     * @param string $gameId
     * @param string $playerId
     * @param int    $column
     *
     * @return array<string, mixed>
     */
    public function move(string $gameId, string $playerId, int $column): array;
}
