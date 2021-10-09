<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query;

use Gaming\ConnectFour\Application\Game\Query\Model\RunningGames\RunningGames;
use Gaming\ConnectFour\Application\Game\Query\Model\RunningGames\RunningGameStore;

final class RunningGamesHandler
{
    private RunningGameStore $runningGamesFinder;

    public function __construct(RunningGameStore $runningGamesFinder)
    {
        $this->runningGamesFinder = $runningGamesFinder;
    }

    public function __invoke(RunningGamesQuery $query): RunningGames
    {
        return new RunningGames(
            $this->runningGamesFinder->count()
        );
    }
}
