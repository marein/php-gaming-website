<?php
declare(strict_types=1);

namespace Gaming\Chat\Infrastructure\Messaging;

use Gaming\Chat\Application\ChatService;
use Gaming\Common\MessageBroker\Consumer;
use Gaming\Common\MessageBroker\Message\Message;

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
    public function handle(Message $message): void
    {
        $payload = json_decode($message->body(), true);

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
        return ['Chat.InitiateChat'];
    }

    /**
     * @inheritdoc
     */
    public function queueName(): string
    {
        return 'Chat.CommandListener';
    }
}
