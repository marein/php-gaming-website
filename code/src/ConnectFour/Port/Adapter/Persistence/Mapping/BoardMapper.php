<?php
declare(strict_types=1);

namespace Gambling\ConnectFour\Port\Adapter\Persistence\Mapping;

use Gambling\Common\ObjectMapper\Collection\ArrayMapper;
use Gambling\Common\ObjectMapper\Mapper;
use Gambling\Common\ObjectMapper\ObjectMapper;
use Gambling\ConnectFour\Domain\Game\Board\Board;

final class BoardMapper implements Mapper
{
    /**
     * @var ObjectMapper $objectMapper
     */
    private $objectMapper;

    /**
     * BoardMapper constructor.
     *
     * @param SizeMapper  $sizeMapper
     * @param FieldMapper $fieldMapper
     */
    public function __construct(SizeMapper $sizeMapper, FieldMapper $fieldMapper)
    {
        $objectMapper = new ObjectMapper(Board::class);
        $objectMapper->addProperty('size', $sizeMapper);
        $objectMapper->addProperty('fields', new ArrayMapper($fieldMapper));
        $objectMapper->addProperty('lastUsedField', $fieldMapper);

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
