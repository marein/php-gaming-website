<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Mapping;

use Gaming\Common\ObjectMapper\Mapper;
use Gaming\ConnectFour\Domain\Game\Board\Field;
use Gaming\ConnectFour\Domain\Game\Board\Point;
use Gaming\ConnectFour\Domain\Game\Board\Stone;

final class FieldMapper implements Mapper
{
    /**
     * @inheritdoc
     */
    public function serialize($value)
    {
        /** @var Field $value */
        return sprintf(
            '%s|%s|%s',
            $value->point()->x(),
            $value->point()->y(),
            (string)$value
        );
    }

    /**
     * @inheritdoc
     */
    public function deserialize($value)
    {
        [$x, $y, $color] = explode('|', $value);
        $point = new Point((int)$x, (int)$y);

        return match ((int)$color) {
            Stone::red()->color() => Field::empty($point)->placeStone(Stone::red()),
            Stone::yellow()->color() => Field::empty($point)->placeStone(Stone::yellow()),
            default => Field::empty($point)
        };
    }
}
