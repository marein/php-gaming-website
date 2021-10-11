<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Jms;

use Gaming\ConnectFour\Domain\Game\GameId;
use JMS\Serializer\Context;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;

final class GameIdSubscriber implements SubscribingHandlerInterface
{
    /**
     * @return string[][]
     */
    public static function getSubscribingMethods(): array
    {
        return [
            [
                'format' => 'json',
                'type' => GameId::class
            ]
        ];
    }

    /**
     * @param array<mixed, mixed> $type
     */
    public function serializeGameIdToJson(
        JsonSerializationVisitor $visitor,
        GameId $gameId,
        array $type,
        Context $context
    ): string {
        return $gameId->toString();
    }

    /**
     * @param array<mixed, mixed> $type
     */
    public function deserializeGameIdFromJson(
        JsonDeserializationVisitor $visitor,
        string $gameId,
        array $type,
        Context $context
    ): GameId {
        return GameId::fromString($gameId);
    }
}
