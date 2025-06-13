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
                ? MoveTimer::set((int)$parts[0])
                : throw new InvalidArgumentException('Format: move:<msPerMove>'),
            'game' => count($parts) === 2
                ? GameTimer::set((int)$parts[0], (int)$parts[1])
                : throw new InvalidArgumentException('Format: game:<baseMs>:<incrementMs>'),
            default => throw new InvalidArgumentException('Unknown timer: ' . $type),
        };
    }
}
