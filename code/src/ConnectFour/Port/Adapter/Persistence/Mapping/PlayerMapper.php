<?php

namespace Gambling\ConnectFour\Port\Adapter\Persistence\Mapping;

use Gambling\Common\ObjectMapper\Mapper;
use Gambling\Common\ObjectMapper\ObjectMapper;
use Gambling\Common\ObjectMapper\Scalar\StringMapper;
use Gambling\ConnectFour\Domain\Game\Player;

final class PlayerMapper implements Mapper
{
    /**
     * @var ObjectMapper $objectMapper
     */
    private $objectMapper;

    /**
     * PlayerMapper constructor.
     *
     * @param StoneMapper $stoneMapper
     */
    public function __construct(StoneMapper $stoneMapper)
    {
        $objectMapper = new ObjectMapper(Player::class);
        $objectMapper->addProperty('playerId', new StringMapper());
        $objectMapper->addProperty('stone', $stoneMapper);

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
