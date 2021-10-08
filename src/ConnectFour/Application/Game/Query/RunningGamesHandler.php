<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query;

use Gaming\ConnectFour\Application\Game\Query\Model\RunningGames\RunningGames;
use Gaming\ConnectFour\Application\Game\Query\Model\RunningGames\RunningGameStore;

final class RunningGamesHandler
{
    /**
     * @var RunningGameStore
     */
    private RunningGameStore $runningGamesFinder;

    /**
     * RunningGamesHandler constructor.
     *
     * @param RunningGameStore $runningGamesFinder
     */
    public function __construct(RunningGameStore $runningGamesFinder)
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
        return new RunningGames(
            $this->runningGamesFinder->count()
        );
    }
}
