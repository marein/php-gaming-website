<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query;

use Gaming\ConnectFour\Application\Game\Query\Model\RunningGames\RunningGames;
use Gaming\ConnectFour\Application\Game\Query\Model\RunningGames\RunningGamesFinder;

final class RunningGamesHandler
{
    /**
     * @var RunningGamesFinder
     */
    private $runningGamesFinder;

    /**
     * RunningGamesHandler constructor.
     *
     * @param RunningGamesFinder $runningGamesFinder
     */
    public function __construct(RunningGamesFinder $runningGamesFinder)
    {
        $this->runningGamesFinder = $runningGamesFinder;
    }

    /**
     * @param RunningGamesQuery $query
     *
     * @return RunningGames
     */
    public function __invoke(RunningGamesQuery $query): RunningGames
    {
        return $this->runningGamesFinder->all();
    }
}
