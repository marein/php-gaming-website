<?php

namespace Gambling\ConnectFour\Port\Adapter\Persistence\Mapping;

use Gambling\Common\ObjectMapper\Mapper;
use Gambling\ConnectFour\Domain\Game\GameId;

final class GameIdMapper implements Mapper
{
    /**
     * @inheritdoc
     */
    public function serialize($value)
    {
        return $value->toString();
    }

    /**
     * @inheritdoc
     */
    public function deserialize($value)
    {
        return GameId::fromString($value);
    }
}
