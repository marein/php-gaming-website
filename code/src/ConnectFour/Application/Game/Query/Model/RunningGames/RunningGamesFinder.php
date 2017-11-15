<?php

namespace Gambling\ConnectFour\Application\Game\Query\Model\RunningGames;

interface RunningGamesFinder
{
    /**
     * @return RunningGames
     */
    public function all(): RunningGames;
}
