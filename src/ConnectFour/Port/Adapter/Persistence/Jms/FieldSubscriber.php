<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Jms;

use Gaming\ConnectFour\Domain\Game\Board\Field;
use Gaming\ConnectFour\Domain\Game\Board\Point;
use Gaming\ConnectFour\Domain\Game\Board\Stone;
use JMS\Serializer\Context;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;

final class FieldSubscriber implements SubscribingHandlerInterface
{
    /**
     * @return string[][]
     */
    public static function getSubscribingMethods(): array
    {
        return [
            [
                'format' => 'json',
                'type' => Field::class
            ],
        ];
    }

    /**
     * @param array<mixed, mixed> $type
     */
    public function serializeFieldToJson(
        JsonSerializationVisitor $visitor,
        Field $field,
        array $type,
        Context $context
    ): string {
        return implode(
            '|',
            [
                $field->point()->x(),
                $field->point()->y(),
                $field->stone()->color()
            ]
        );
    }

    /**
     * @param array<mixed, mixed> $type
     */
    public function deserializeFieldFromJson(
        JsonDeserializationVisitor $visitor,
        string $field,
        array $type,
        Context $context
    ): Field {
        [$x, $y, $color] = explode('|', $field);
        $point = new Point((int)$x, (int)$y);

        return match ((int)$color) {
            Stone::red()->color() => Field::empty($point)->placeStone(Stone::red()),
            Stone::yellow()->color() => Field::empty($point)->placeStone(Stone::yellow()),
            default => Field::empty($point)
        };
    }
}
