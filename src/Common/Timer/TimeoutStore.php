<?php

declare(strict_types=1);

namespace Gaming\Common\Timer;

interface TimeoutStore
{
    /**
     * @param array<string, int> $timeouts The key is the timeout id and the value is the
     *                                     time in ms when the timeout should be handled.
     */
    public function add(array $timeouts): void;

    /**
     * @param string[] $timeoutIds
     */
    public function remove(array $timeoutIds): void;

    /**
     * @return array<string, int> The key is the timeout id and the value is the
     *                            time in ms when the timeout should be handled.
     */
    public function find(int $maxTimeMs, int $limit = 1000): array;
}
