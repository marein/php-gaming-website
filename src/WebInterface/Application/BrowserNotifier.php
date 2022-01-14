<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Application;

interface BrowserNotifier
{
    public function publish(string $channel, string $message): void;
}
