<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query;

use Gaming\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGames;
use Gaming\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGameStore;

final class OpenGamesHandler
{
    public function __construct(
        private readonly OpenGameStore $openGameStore
    ) {
    }

    public function __invoke(OpenGamesQuery $query): OpenGames
    {
        return $this->openGameStore->all($query->limit);
    }
}
