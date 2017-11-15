<?php

namespace Gambling\ConnectFour\Port\Adapter\Persistence\Repository;

use Gambling\ConnectFour\Application\Game\Query\Model\Game\Field;
use Gambling\ConnectFour\Application\Game\Query\Model\Game\Game;
use Gambling\ConnectFour\Application\Game\Query\Model\Game\GameFinder;
use Gambling\ConnectFour\Port\Adapter\Persistence\Projection\PredisGameProjection;
use Predis\Client;

final class PredisGameFinder implements GameFinder
{
    /**
     * @var Client
     */
    private $predis;

    /**
     * PredisGameFinder constructor.
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
    public function find(string $gameId): ?Game
    {
        $gameAsJson = $this->predis->get(
            PredisGameProjection::STORAGE_KEY_PREFIX . $gameId
        );

        if ($gameAsJson === null) {
            return null;
        }

        $gameAsArray = json_decode(
            $gameAsJson,
            true
        );

        return $this->mapArrayToGame($gameAsArray);
    }

    /**
     * @param array $gameAsArray
     *
     * @return Game
     */
    private function mapArrayToGame(array $gameAsArray): Game
    {
        return new Game(
            $gameAsArray['gameId'],
            $gameAsArray['chatId'],
            $gameAsArray['width'],
            $gameAsArray['height'],
            $gameAsArray['finished'],
            array_map(function ($fieldAsArray) {
                return new Field(
                    $fieldAsArray['x'],
                    $fieldAsArray['y'],
                    $fieldAsArray['color']
                );
            }, $gameAsArray['fields'])
        );
    }
}
