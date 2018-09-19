<?php
declare(strict_types=1);

namespace Gambling\ConnectFour\Application\Game\Query;

use Gambling\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayer;
use Gambling\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayerFinder;

final class GamesByPlayerHandler
{
    /**
     * @var GamesByPlayerFinder
     */
    private $gamesByPlayerFinder;

    /**
     * GameHandler constructor.
     *
     * @param GamesByPlayerFinder $gamesByPlayerFinder
     */
    public function __construct(GamesByPlayerFinder $gamesByPlayerFinder)
    {
        $this->gamesByPlayerFinder = $gamesByPlayerFinder;
    }

    /**
     * @param GamesByPlayerQuery $query
     *
     * @return GamesByPlayer
     */
    public function __invoke(GamesByPlayerQuery $query): GamesByPlayer
    {
        return $this->gamesByPlayerFinder->all(
            $query->playerId()
        );
    }
}
