<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\Model\OpenGames;

interface OpenGamesFinder
{
    /**
     * @return OpenGames
     */
    public function all(): OpenGames;
}
