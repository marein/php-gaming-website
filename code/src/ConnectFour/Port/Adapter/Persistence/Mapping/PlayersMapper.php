<?php

namespace Gambling\ConnectFour\Port\Adapter\Persistence\Mapping;

use Gambling\Common\ObjectMapper\Exception\MapperException;
use Gambling\Common\ObjectMapper\Mapper;
use Gambling\Common\ObjectMapper\ObjectMapper;
use Gambling\ConnectFour\Domain\Game\Players;

final class PlayersMapper implements Mapper
{
    /**
     * @var ObjectMapper $objectMapper
     */
    private $objectMapper;

    /**
     * PlayersMapper constructor.
     *
     * @param PlayerMapper $playerMapper
     *
     * @throws MapperException
     */
    public function __construct(PlayerMapper $playerMapper)
    {
        $objectMapper = new ObjectMapper(Players::class);
        $objectMapper->addProperty('currentPlayer', $playerMapper);
        $objectMapper->addProperty('nextPlayer', $playerMapper);

        $this->objectMapper = $objectMapper;
    }

    /**
     * @inheritdoc
     */
    public function serialize($value)
    {
        return $this->objectMapper->serialize($value);
    }

    /**
     * @inheritdoc
     */
    public function deserialize($value)
    {
        return $this->objectMapper->deserialize($value);
    }
}
