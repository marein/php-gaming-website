<?php

declare(strict_types=1);

namespace Gaming\Chat\Application;

use Gaming\Chat\Application\Command\InitiateChatCommand;
use Gaming\Chat\Application\Command\WriteMessageCommand;
use Gaming\Chat\Application\Event\ChatInitiated;
use Gaming\Chat\Application\Event\MessageWritten;
use Gaming\Chat\Application\Exception\AuthorNotAllowedException;
use Gaming\Chat\Application\Exception\ChatNotFoundException;
use Gaming\Chat\Application\Exception\EmptyMessageException;
use Gaming\Chat\Application\Query\MessagesQuery;
use Gaming\Common\Clock\Clock;
use Gaming\Common\EventStore\EventStore;

/**
 * Although this class is an application service, it still contains business logic.
 * The business logic is very limited. There's no need for a fully fledged domain model.
 */
final class ChatService
{
    private ChatGateway $chatGateway;

    private EventStore $eventStore;

    public function __construct(
        ChatGateway $chatGateway,
        EventStore $eventStore
    ) {
        $this->chatGateway = $chatGateway;
        $this->eventStore = $eventStore;
    }

    public function initiateChat(InitiateChatCommand $initiateChatCommand): string
    {
        $chatId = $this->chatGateway->create($initiateChatCommand->authors());

        $this->eventStore->append(
            new ChatInitiated($chatId)
        );

        return $chatId->toString();
    }

    /**
     * @throws ChatNotFoundException
     * @throws AuthorNotAllowedException
     * @throws EmptyMessageException
     */
    public function writeMessage(WriteMessageCommand $writeMessageCommand): void
    {
        $chatId = ChatId::fromString($writeMessageCommand->chatId());
        $authorId = $writeMessageCommand->authorId();
        $message = trim(substr($writeMessageCommand->message(), 0, 140));

        if ($message === '') {
            throw new EmptyMessageException();
        }

        $chat = $this->chatGateway->byId($chatId);
        $authors = json_decode($chat['authors'], true, 512, JSON_THROW_ON_ERROR);

        // If authors are assigned to the chat, only those authors can write messages.
        if (!empty($authors) && !in_array($authorId, $authors, true)) {
            throw new AuthorNotAllowedException();
        }

        $writtenAt = Clock::instance()->now();

        $messageId = $this->chatGateway->createMessage($chatId, $authorId, $message, $writtenAt);

        $this->eventStore->append(
            new MessageWritten($chatId, $messageId, $authorId, $message, $writtenAt)
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     * @throws ChatNotFoundException
     */
    public function messages(MessagesQuery $messagesQuery): array
    {
        return $this->chatGateway->messages(
            ChatId::fromString($messagesQuery->chatId()),
            $messagesQuery->authorId(),
            $messagesQuery->offset(),
            $messagesQuery->limit()
        );
    }
}
