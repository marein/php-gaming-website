<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Mapping;

use Gaming\Common\ObjectMapper\ObjectMapper;
use Gaming\Common\ObjectMapper\Scalar\StringMapper;
use Gaming\ConnectFour\Domain\Game\Game;

final class GameMapper
{
    /**
     * @var ObjectMapper $objectMapper
     */
    private $objectMapper;

    /**
     * GameMapper constructor.
     *
     * @param GameIdMapper $gameIdMapper
     * @param StateMapper  $stateMapper
     */
    public function __construct(GameIdMapper $gameIdMapper, StateMapper $stateMapper)
    {
        $objectMapper = new ObjectMapper(Game::class);
        $objectMapper->addProperty('gameId', $gameIdMapper);
        $objectMapper->addProperty('state', $stateMapper);
        $objectMapper->addProperty('chatId', new StringMapper());

        $this->objectMapper = $objectMapper;
    }

    /**
     * @inheritdoc
     */
    public function serialize($value): array
    {
        return $this->objectMapper->serialize($value);
    }

    /**
     * @inheritdoc
     */
    public function deserialize($value): Game
    {
        return $this->objectMapper->deserialize($value);
    }
}
