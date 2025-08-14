<?php

declare(strict_types=1);

namespace Gaming\Chat\Infrastructure\Messaging;

use Gaming\Common\BrowserNotifier\BrowserNotifier;
use Gaming\Common\MessageBroker\Context;
use Gaming\Common\MessageBroker\Message;
use Gaming\Common\MessageBroker\MessageHandler;
use Gaming\Common\Usernames\Usernames;
use GamingPlatform\Api\Chat\V1\ChatV1Factory;

final class PublishMessageBrokerEventsToBrowserMessageHandler implements MessageHandler
{
    public function __construct(
        private readonly BrowserNotifier $browserNotifier,
        private readonly Usernames $usernames
    ) {
    }

    public function handle(Message $message, Context $context): void
    {
        match ($message->name()) {
            'Chat.MessageWritten' => $this->handleMessageWritten($message),
            default => true
        };
    }

    private function handleMessageWritten(Message $message): void
    {
        $messageWritten = ChatV1Factory::createMessageWritten($message->body());

        $this->browserNotifier->publish(
            ['chat-' . $message->streamId()],
            $message->name(),
            json_encode(
                [
                    'chatId' => $messageWritten->getChatId(),
                    'messageId' => $messageWritten->getMessageId(),
                    'authorId' => $messageWritten->getAuthorId(),
                    'authorUsername' => $this->usernames
                            ->byIds([$messageWritten->getAuthorId()])[$messageWritten->getAuthorId()] ?? null,
                    'message' => $messageWritten->getMessage(),
                    'writtenAt' => $messageWritten->getWrittenAt(),
                ],
                JSON_THROW_ON_ERROR
            )
        );
    }
}
