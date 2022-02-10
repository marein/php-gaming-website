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
            'ConnectFour.GameOpened', 'ConnectFour.GameAborted' => $this->browserNotifier->publish(
                ['lobby'],
                $json
            ),
            'ConnectFour.GameResigned',
            'ConnectFour.GameWon',
            'ConnectFour.GameDrawn',
            'ConnectFour.PlayerMoved',
            'ConnectFour.ChatAssigned' => $this->browserNotifier->publish(
                ['connect-four-' . $payload['gameId']],
                $json
            ),
            'ConnectFour.PlayerJoined' => $this->browserNotifier->publish(
                ['lobby', 'connect-four-' . $payload['gameId']],
                $json
            ),
            'Chat.MessageWritten' => $this->browserNotifier->publish(
                ['chat-' . $payload['chatId']],
                $json
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
}
