<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Application;

interface ConnectFourService
{
    /**
     * @return array<string, mixed>
     */
    public function openGames(): array;

    /**
     * @return array<string, mixed>
     */
    public function runningGames(): array;

    /**
     * @return array<string, mixed>
     */
    public function gamesByPlayer(string $playerId): array;

    /**
     * @return array<string, mixed>
     */
    public function game(string $gameId): array;

    /**
     * @return array<string, mixed>
     */
    public function open(string $playerId): array;

    /**
     * @return array<string, mixed>
     */
    public function join(string $gameId, string $playerId): array;

    /**
     * @return array<string, mixed>
     */
    public function abort(string $gameId, string $playerId): array;

    /**
     * @return array<string, mixed>
     */
    public function resign(string $gameId, string $playerId): array;

    /**
     * @return array<string, mixed>
     */
    public function move(string $gameId, string $playerId, int $column): array;
}
