<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Application;

interface BrowserNotifier
{
    /**
     * @param string[] $channels
     */
    public function publish(array $channels, string $name, string $message): void;
}
