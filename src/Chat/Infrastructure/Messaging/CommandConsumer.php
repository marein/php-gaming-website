<?php
declare(strict_types=1);

namespace Gaming\Chat\Infrastructure\Messaging;

use Gaming\Chat\Application\ChatService;
use Gaming\Chat\Application\Command\InitiateChatCommand;
use Gaming\Common\MessageBroker\Model\Consumer\Consumer;
use Gaming\Common\MessageBroker\Model\Consumer\Name;
use Gaming\Common\MessageBroker\Model\Message\Message;
use Gaming\Common\MessageBroker\Model\Subscription\SpecificMessage;

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
            new InitiateChatCommand(
                $payload['ownerId'],
                $payload['authors']
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function subscriptions(): array
    {
        return [
            new SpecificMessage('Chat', 'InitiateChat')
        ];
    }

    /**
     * @inheritdoc
     */
    public function name(): Name
    {
        return new Name('Chat', 'CommandListener');
    }
}
