<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Mapping;

use Gaming\Common\ObjectMapper\Exception\MapperException;
use Gaming\Common\ObjectMapper\Mapper;
use Gaming\Common\ObjectMapper\ObjectMapper;
use Gaming\ConnectFour\Domain\Game\Players;

final class PlayersMapper implements Mapper
{
    /**
     * @var ObjectMapper $objectMapper
     */
    private ObjectMapper $objectMapper;

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
