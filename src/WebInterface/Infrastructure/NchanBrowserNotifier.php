<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure;

use Gaming\WebInterface\Application\BrowserNotifier;
use Marein\Nchan\Api\Model\JsonMessage;
use Marein\Nchan\Nchan;

final class NchanBrowserNotifier implements BrowserNotifier
{
    private Nchan $nchan;

    public function __construct(string $baseUrl)
    {
        $this->nchan = new Nchan($baseUrl);
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
