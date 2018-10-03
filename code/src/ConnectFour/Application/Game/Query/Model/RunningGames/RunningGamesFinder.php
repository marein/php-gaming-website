<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\Model\RunningGames;

interface RunningGamesFinder
{
    /**
     * @return RunningGames
     */
    public function all(): RunningGames;
}
