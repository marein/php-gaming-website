<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Mapping;

use Gaming\Common\ObjectMapper\Mapper;
use Gaming\Common\ObjectMapper\ObjectMapper;
use Gaming\Common\ObjectMapper\Scalar\IntMapper;
use Gaming\ConnectFour\Domain\Game\Board\Stone;

final class StoneMapper implements Mapper
{
    /**
     * @var ObjectMapper $objectMapper
     */
    private $objectMapper;

    /**
     * StoneMapper constructor.
     */
    public function __construct()
    {
        $objectMapper = new ObjectMapper(Stone::class);
        $objectMapper->addProperty('color', new IntMapper());

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
