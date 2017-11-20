<?php

namespace Gambling\ConnectFour\Port\Adapter\Persistence\Mapping;

use Gambling\Common\ObjectMapper\Mapper;
use Gambling\Common\ObjectMapper\ObjectMapper;
use Gambling\Common\ObjectMapper\Scalar\IntMapper;
use Gambling\ConnectFour\Domain\Game\Board\Size;

final class SizeMapper implements Mapper
{
    /**
     * @var ObjectMapper $objectMapper
     */
    private $objectMapper;

    /**
     * SizeMapper constructor.
     */
    public function __construct()
    {
        $objectMapper = new ObjectMapper(Size::class);
        $objectMapper->addProperty('width', new IntMapper());
        $objectMapper->addProperty('height', new IntMapper());

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
