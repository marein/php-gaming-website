<?php

namespace Gambling\ConnectFour\Application\Game\Query;

use Gambling\ConnectFour\Application\Game\Query\Exception\GameNotFoundException;
use Gambling\ConnectFour\Application\Game\Query\Model\Game\Game;
use Gambling\ConnectFour\Application\Game\Query\Model\Game\GameFinder;
use Gambling\ConnectFour\Domain\Game\GameId;
use Gambling\ConnectFour\Domain\Game\Games;

final class GameHandler
{
    /**
     * @var GameFinder
     */
    private $gameFinder;

    /**
     * @var Games
     */
    private $games;

    /**
     * GameHandler constructor.
     *
     * @param GameFinder $gameFinder
     * @param Games      $games
     */
    public function __construct(GameFinder $gameFinder, Games $games)
    {
        $this->gameFinder = $gameFinder;
        $this->games = $games;
    }

    public function __invoke(GameQuery $query): Game
    {
        $gameId = $query->gameId();

        if ($game = $this->gameFinder->find($gameId)) {
            return $game;
        }

        // Query the command storage to fill the eventual consistency lag.
        // This happens when a player joins and both players are redirected to the game page.
        // It can happen, that the game is not in the query database yet. So, query the command database and
        // transform the Game from the Domain Model to the Game from the Query Model.
        if ($domainGame = $this->games->get(GameId::fromString($gameId))) {
            return Game::fromGame($domainGame);
        }

        throw new GameNotFoundException();
    }
}
