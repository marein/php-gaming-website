<?php

namespace Gambling\WebInterface\Infrastructure;

use Gambling\WebInterface\Application\BrowserNotifier;
use Marein\Nchan\Api\Model\JsonMessage;
use Marein\Nchan\HttpAdapter\BasicAuthenticationCredentials;
use Marein\Nchan\HttpAdapter\HttpStreamWrapperClient;
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
     * @param string $username
     * @param string $password
     */
    public function __construct(string $baseUrl, string $username, string $password)
    {
        $this->nchan = new Nchan(
            $baseUrl,
            new HttpStreamWrapperClient(
                new BasicAuthenticationCredentials(
                    $username,
                    $password
                )
            )
        );
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
