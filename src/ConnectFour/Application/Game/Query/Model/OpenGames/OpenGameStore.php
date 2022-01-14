<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\Model\OpenGames;

interface OpenGameStore
{
    public function save(OpenGame $openGame): void;

    public function remove(string $gameId): void;

    public function all(): OpenGames;
}
