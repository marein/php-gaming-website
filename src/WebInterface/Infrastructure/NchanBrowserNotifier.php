<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure;

use Gaming\WebInterface\Application\BrowserNotifier;
use Marein\Nchan\Api\Model\JsonMessage;
use Marein\Nchan\Nchan;

final class NchanBrowserNotifier implements BrowserNotifier
{
    public function __construct(
        private readonly Nchan $nchan
    ) {
    }

    public function publish(array $channels, string $message): void
    {
        $this->nchan->channel('/pub?id=' . implode(',', $channels))->publish(
            new JsonMessage(
                '',
                $message
            )
        );
    }
}
