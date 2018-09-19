<?php
declare(strict_types=1);

namespace Gambling\ConnectFour\Port\Adapter\Persistence\Repository;

use Gambling\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGame;
use Gambling\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGames;
use Gambling\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGamesFinder;
use Gambling\ConnectFour\Port\Adapter\Persistence\Projection\PredisOpenGamesProjection;
use Predis\Client;

final class PredisOpenGamesFinder implements OpenGamesFinder
{
    /**
     * @var Client
     */
    private $predis;

    /**
     * PredisOpenGamesFinder constructor.
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
    public function all(): OpenGames
    {
        return new OpenGames(
            array_values(
                array_map(function ($value) {
                    $payload = json_decode($value, true);
                    return new OpenGame($payload['gameId'], $payload['playerId']);
                }, $this->predis->hgetall(PredisOpenGamesProjection::STORAGE_KEY))
            )
        );
    }
}
