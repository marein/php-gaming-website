<?php

namespace Gaming\ConnectFour\Domain\Game\Timer;

use Exception;

final class TimerFactory
{
    /**
     * @throws Exception
     */
    public static function fromString(string $config): Timer
    {
        $parts = explode(':', $config);
        $type = array_shift($parts);

        return match ($type) {
            'move' => count($parts) === 1
                ? TimePerMove::set((int)$parts[0])
                : throw new Exception('Format: move:<durationMs>'),
            'game' => count($parts) === 1
                ? TimePerGame::set((int)$parts[0])
                : throw new Exception('Format: game:<durationMs>'),
            'fischer' => count($parts) === 2
                ? Fischer::set((int)$parts[0], (int)$parts[1])
                : throw new Exception('Format: fischer:<baseSeconds>:<incrementSeconds>'),
            default => throw new Exception('Unknown timer type: ' . $type),
        };
    }
}
