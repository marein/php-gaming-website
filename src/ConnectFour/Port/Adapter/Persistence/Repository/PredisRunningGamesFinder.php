<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Gaming\ConnectFour\Application\Game\Query\Model\RunningGames\RunningGames;
use Gaming\ConnectFour\Application\Game\Query\Model\RunningGames\RunningGamesFinder;
use Gaming\ConnectFour\Port\Adapter\Persistence\Projection\PredisRunningGamesProjection;
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
