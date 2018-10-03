<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Mapping;

use Gaming\Common\ObjectMapper\Mapper;
use Gaming\Common\ObjectMapper\ObjectMapper;
use Gaming\Common\ObjectMapper\Scalar\StringMapper;
use Gaming\ConnectFour\Domain\Game\Player;

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
