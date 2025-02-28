<?php

declare(strict_types=1);

namespace Gaming\Common\BrowserNotifier\Integration;

use Gaming\Common\BrowserNotifier\BrowserNotifier;
use Marein\Nchan\Api\Model\PlainTextMessage;
use Marein\Nchan\Nchan;

final class NchanBrowserNotifier implements BrowserNotifier
{
    public function __construct(
        private readonly Nchan $nchan
    ) {
    }

    public function publish(array $channels, string $name, string $message): void
    {
        $this->nchan->channel('/pub?id=' . implode(',', $channels))->publish(
            new PlainTextMessage(
                '',
                $name . ':' . $message
            )
        );
    }
}
