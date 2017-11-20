<?php

namespace Gambling\ConnectFour\Port\Adapter\Persistence\Mapping;

use Gambling\Common\ObjectMapper\Mapper;
use Gambling\ConnectFour\Domain\Game\Board\Field;
use Gambling\ConnectFour\Domain\Game\Board\Point;
use Gambling\ConnectFour\Domain\Game\Board\Stone;

final class FieldMapper implements Mapper
{
    /**
     * @inheritdoc
     */
    public function serialize($value)
    {
        // The field can be null in Board::lastUsedField
        if ($value === null) {
            return null;
        }

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
        // The field can be null in Board::lastUsedField
        if ($value === null) {
            return null;
        }

        [$x, $y, $color] = explode('|', $value);
        $x = (int)$x;
        $y = (int)$y;
        $color = (int)$color;

        $point = new Point($x, $y);
        $field = Field::empty($point);

        if ($color == Stone::RED) {
            $field = $field->placeStone(Stone::red());
        } elseif ($color == STONE::YELLOW) {
            $field = $field->placeStone(Stone::yellow());
        }

        return $field;
    }
}
