<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Jms;

use Gaming\ConnectFour\Domain\Game\Board\Stone;
use JMS\Serializer\Context;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;

final class StoneSubscriber implements SubscribingHandlerInterface
{
    /**
     * @return string[][]
     */
    public static function getSubscribingMethods(): array
    {
        return [
            [
                'format' => 'json',
                'type' => Stone::class
            ],
        ];
    }

    /**
     * @param array<mixed, mixed> $type
     *
     * @return array<string, mixed>
     */
    public function serializeStoneToJson(
        JsonSerializationVisitor $visitor,
        Stone $stone,
        array $type,
        Context $context
    ): array {
        return ['color' => $stone->value];
    }

    /**
     * @param array<string, mixed> $stone
     * @param array<mixed, mixed> $type
     */
    public function deserializeStoneFromJson(
        JsonDeserializationVisitor $visitor,
        array $stone,
        array $type,
        Context $context
    ): Stone {
        return Stone::from($stone['color'] ?? -1);
    }
}
