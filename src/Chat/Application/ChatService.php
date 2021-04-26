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
    /**
     * @var ChatGateway
     */
    private ChatGateway $chatGateway;

    /**
     * @var EventStore
     */
    private EventStore $eventStore;

    /**
     * ChatService constructor.
     *
     * @param ChatGateway          $chatGateway
     * @param EventStore           $eventStore
     */
    public function __construct(
        ChatGateway $chatGateway,
        EventStore $eventStore
    ) {
        $this->chatGateway = $chatGateway;
        $this->eventStore = $eventStore;
    }

    /**
     * Initiate a new chat.
     *
     * @param InitiateChatCommand $initiateChatCommand
     *
     * @return string
     */
    public function initiateChat(InitiateChatCommand $initiateChatCommand): string
    {
        $chatId = $this->chatGateway->create($initiateChatCommand->ownerId(), $initiateChatCommand->authors());

        $this->eventStore->append(
            new ChatInitiated($chatId, $initiateChatCommand->ownerId())
        );

        return $chatId->toString();
    }

    /**
     * Write a message to the chat.
     *
     * @param WriteMessageCommand $writeMessageCommand
     *
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

        $ownerId = $chat['ownerId'];
        $writtenAt = Clock::instance()->now();

        $messageId = $this->chatGateway->createMessage($chatId, $authorId, $message, $writtenAt);

        $this->eventStore->append(
            new MessageWritten($chatId, $messageId, $ownerId, $authorId, $message, $writtenAt)
        );
    }

    /**
     * Get messages by chat.
     *
     * @param MessagesQuery $messagesQuery
     *
     * @return array<int, array<string, mixed>>
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
