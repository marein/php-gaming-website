<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer;

use Gaming\ConnectFour\Application\Game\Query\PlayerSearchStatistics\PlayerSearchStatisticsResponse;

interface GamesByPlayerStore
{
    public function addRunning(string $gameId, string $playerOne, string $playerTwo): void;

    public function addDraw(string $gameId, string $playerOne, string $playerTwo): void;

    public function addWin(string $gameId, string $winnerId, string $loserId): void;

    public function addAbort(string $gameId, string $playerOne, string $playerTwo): void;

    public function searchStatistics(string $playerId): PlayerSearchStatisticsResponse;

    public function search(string $playerId, State $state, int $page, int $limit): GamesByPlayer;
}
