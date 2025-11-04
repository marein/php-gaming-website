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
        $joinedChannels = implode(',', $channels);

        $this->nchan->channel('/pub?id=' . $joinedChannels)->publish(
            new PlainTextMessage(
                '',
                $name . ':' . $joinedChannels . ':' . $message
            )
        );
    }
}
