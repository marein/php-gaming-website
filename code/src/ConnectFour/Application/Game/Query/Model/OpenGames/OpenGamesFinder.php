<?php

namespace Gambling\ConnectFour\Application\Game\Query\Model\OpenGames;

interface OpenGamesFinder
{
    /**
     * @return OpenGames
     */
    public function all(): OpenGames;
}
