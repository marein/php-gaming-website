<?php
declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure;

use Gaming\WebInterface\Application\BrowserNotifier;
use Marein\Nchan\Api\Model\JsonMessage;
use Marein\Nchan\Nchan;

final class NchanBrowserNotifier implements BrowserNotifier
{
    /**
     * @var Nchan
     */
    private $nchan;

    /**
     * NchanBrowserNotifier constructor.
     *
     * @param string $baseUrl
     */
    public function __construct(string $baseUrl)
    {
        $this->nchan = new Nchan($baseUrl);
    }

    /**
     * @inheritdoc
     */
    public function publish(string $channel, string $message): void
    {
        $this->nchan->channel($channel)->publish(
            new JsonMessage(
                '',
                $message
            )
        );
    }
}
