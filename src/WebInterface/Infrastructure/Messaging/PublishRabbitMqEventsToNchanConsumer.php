<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Messaging;

use Gaming\Common\MessageBroker\Model\Consumer\Consumer;
use Gaming\Common\MessageBroker\Model\Consumer\Name;
use Gaming\Common\MessageBroker\Model\Context\Context;
use Gaming\Common\MessageBroker\Model\Message\Message;
use Gaming\Common\MessageBroker\Model\Subscription\SpecificMessage;
use Gaming\Common\MessageBroker\Model\Subscription\WholeDomain;
use Gaming\WebInterface\Application\BrowserNotifier;

final class PublishRabbitMqEventsToNchanConsumer implements Consumer
{
    private BrowserNotifier $browserNotifier;

    public function __construct(BrowserNotifier $browserNotifier)
    {
        $this->browserNotifier = $browserNotifier;
    }

    public function handle(Message $message, Context $context): void
    {
        $name = (string)$message->name();
        $payload = array_merge(
            json_decode($message->body(), true, 512, JSON_THROW_ON_ERROR),
            ['eventName' => $name]
        );
        $json = json_encode($payload, JSON_THROW_ON_ERROR);

        match ($name) {
            'ConnectFour.GameOpened', 'ConnectFour.GameAborted' => $this->publishToBrowser(
                $json,
                ['lobby']
            ),
            'ConnectFour.GameResigned',
            'ConnectFour.GameWon',
            'ConnectFour.GameDrawn',
            'ConnectFour.PlayerMoved',
            'ConnectFour.ChatAssigned' => $this->publishToBrowser(
                $json,
                ['game-' . $payload['gameId']]
            ),
            'ConnectFour.PlayerJoined' => $this->publishToBrowser(
                $json,
                ['lobby', 'game-' . $payload['gameId']]
            ),
            'Chat.MessageWritten' => $this->publishToBrowser(
                $json,
                ['game-' . $payload['ownerId']]
            ),
            default => true
        };
    }

    public function subscriptions(): array
    {
        return [
            new WholeDomain('ConnectFour'),
            new SpecificMessage('Chat', 'MessageWritten')
        ];
    }

    public function name(): Name
    {
        return new Name('WebInterface', 'BrowserNotification');
    }

    /**
     * @param string[] $channels
     */
    private function publishToBrowser(string $body, array $channels): void
    {
        foreach ($channels as $channel) {
            $this->browserNotifier->publish('/pub?id=' . $channel, $body);
        }
    }
}
