<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Mapping;

use Gaming\Common\ObjectMapper\Mapper;
use Gaming\ConnectFour\Domain\Game\GameId;

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
