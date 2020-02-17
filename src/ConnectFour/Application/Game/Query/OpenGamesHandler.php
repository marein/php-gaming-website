<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query;

use Gaming\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGames;
use Gaming\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGameStore;

final class OpenGamesHandler
{
    /**
     * @var OpenGameStore
     */
    private OpenGameStore $openGameStore;

    /**
     * OpenGamesHandler constructor.
     *
     * @param OpenGameStore $openGameStore
     */
    public function __construct(OpenGameStore $openGameStore)
    {
        $this->openGameStore = $openGameStore;
    }

    /**
     * @param OpenGamesQuery $query
     *
     * @return OpenGames
     */
    public function __invoke(OpenGamesQuery $query): OpenGames
    {
        return $this->openGameStore->all();
    }
}
