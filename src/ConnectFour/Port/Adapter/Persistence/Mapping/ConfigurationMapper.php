<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Mapping;

use Gaming\Common\ObjectMapper\Mapper;
use Gaming\Common\ObjectMapper\ObjectMapper;
use Gaming\ConnectFour\Domain\Game\Configuration;

final class ConfigurationMapper implements Mapper
{
    /**
     * @var ObjectMapper $objectMapper
     */
    private ObjectMapper $objectMapper;

    /**
     * ConfigurationMapper constructor.
     *
     * @param SizeMapper        $sizeMapper
     * @param WinningRuleMapper $winningRuleMapper
     */
    public function __construct(SizeMapper $sizeMapper, WinningRuleMapper $winningRuleMapper)
    {
        $objectMapper = new ObjectMapper(Configuration::class);
        $objectMapper->addProperty('size', $sizeMapper);
        $objectMapper->addProperty('winningRule', $winningRuleMapper);

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
