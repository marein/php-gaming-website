<?php

namespace Gambling\Chat\Infrastructure\Messaging;

use Gambling\Chat\Application\ChatService;
use Gambling\Common\MessageBroker\Consumer;

final class CommandConsumer implements Consumer
{
    /**
     * @var ChatService
     */
    private $chatService;

    /**
     * CommandConsumer constructor.
     *
     * @param ChatService $chatService
     */
    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * @inheritdoc
     */
    public function handle(string $body, string $routingKey): void
    {
        $payload = json_decode($body, true);

        $this->chatService->initiateChat(
            $payload['ownerId'],
            $payload['authors']
        );
    }

    /**
     * @inheritdoc
     */
    public function routingKeys(): array
    {
        return ['chat.initiate-chat'];
    }

    /**
     * @inheritdoc
     */
    public function queueName(): string
    {
        return 'chat.command-listener';
    }
}
