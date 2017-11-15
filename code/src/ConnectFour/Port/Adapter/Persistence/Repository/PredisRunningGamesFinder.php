<?php

namespace Gambling\ConnectFour\Port\Adapter\Persistence\Repository;

use Gambling\ConnectFour\Application\Game\Query\Model\RunningGames\RunningGames;
use Gambling\ConnectFour\Application\Game\Query\Model\RunningGames\RunningGamesFinder;
use Gambling\ConnectFour\Port\Adapter\Persistence\Projection\PredisRunningGamesProjection;
use Predis\Client;

final class PredisRunningGamesFinder implements RunningGamesFinder
{
    /**
     * @var Client
     */
    private $predis;

    /**
     * PredisRunningGamesFinder constructor.
     *
     * @param Client $predis
     */
    public function __construct(Client $predis)
    {
        $this->predis = $predis;
    }

    /**
     * @inheritdoc
     */
    public function all(): RunningGames
    {
        return new RunningGames(
            (int)$this->predis->scard(PredisRunningGamesProjection::STORAGE_KEY)
        );
    }
}
