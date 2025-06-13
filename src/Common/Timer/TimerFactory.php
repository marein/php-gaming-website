<?php

namespace Gaming\Common\Timer;

final class TimerFactory
{
    public static function fromString(string $config): ?Timer
    {
        $parts = explode(':', $config);
        $type = array_shift($parts);

        return match ($type) {
            'move' => count($parts) === 1 ? MoveTimer::set((int)$parts[0]) : MoveTimer::set(15000),
            'game' => count($parts) === 2 ? GameTimer::set((int)$parts[0], (int)$parts[1]) : GameTimer::set(60000, 0),
            default => null
        };
    }
}
