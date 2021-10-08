<?php

declare(strict_types=1);

namespace Gaming\Chat\Infrastructure\Messaging;

use Gaming\Chat\Application\Command\InitiateChatCommand;
use Gaming\Common\Bus\Bus;
use Gaming\Common\MessageBroker\Model\Consumer\Consumer;
use Gaming\Common\MessageBroker\Model\Consumer\Name;
use Gaming\Common\MessageBroker\Model\Message\Message;
use Gaming\Common\MessageBroker\Model\Subscription\SpecificMessage;

final class CommandConsumer implements Consumer
{
    private Bus $commandBus;

    public function __construct(Bus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function handle(Message $message): void
    {
        $payload = json_decode($message->body(), true, 512, JSON_THROW_ON_ERROR);

        $this->commandBus->handle(
            new InitiateChatCommand(
                $payload['ownerId'],
                $payload['authors']
            )
        );
    }

    public function subscriptions(): array
    {
        return [
            new SpecificMessage('Chat', 'InitiateChat')
        ];
    }

    public function name(): Name
    {
        return new Name('Chat', 'CommandListener');
    }
}
