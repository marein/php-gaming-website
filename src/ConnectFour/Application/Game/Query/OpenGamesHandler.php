<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query;

use Gaming\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGames;
use Gaming\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGameStore;

final class OpenGamesHandler
{
    private OpenGameStore $openGameStore;

    public function __construct(OpenGameStore $openGameStore)
    {
        $this->openGameStore = $openGameStore;
    }

    public function __invoke(OpenGamesQuery $query): OpenGames
    {
        return $this->openGameStore->all();
    }
}
