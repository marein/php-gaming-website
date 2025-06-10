<?php

namespace Gaming\Common\Timer;

use InvalidArgumentException;

final class TimerFactory
{
    /**
     * @throws InvalidArgumentException
     */
    public static function fromString(string $config): Timer
    {
        $parts = explode(':', $config);
        $type = array_shift($parts);

        return match ($type) {
            'move' => count($parts) === 1
                ? TimePerMove::set((int)$parts[0])
                : throw new InvalidArgumentException('Format: move:<secondsPerMove>'),
            'game' => count($parts) === 2
                ? TimePerGame::set((int)$parts[0], (int)$parts[1])
                : throw new InvalidArgumentException('Format: game:<baseSeconds>:<incrementSeconds>'),
            default => throw new InvalidArgumentException('Unknown timer type: ' . $type),
        };
    }
}
