<?php

declare(strict_types=1);

namespace Gaming\Chat\Application;

use Gaming\Chat\Application\Command\InitiateChatCommand;
use Gaming\Chat\Application\Command\WriteMessageCommand;
use Gaming\Chat\Application\Event\ChatInitiated;
use Gaming\Chat\Application\Event\MessageWritten;
use Gaming\Chat\Application\Exception\AuthorNotAllowedException;
use Gaming\Chat\Application\Exception\ChatAlreadyExistsException;
use Gaming\Chat\Application\Exception\ChatNotFoundException;
use Gaming\Chat\Application\Exception\EmptyMessageException;
use Gaming\Chat\Application\Query\MessagesQuery;
use Gaming\Common\EventStore\EventStore;
use Gaming\Common\IdempotentStorage\IdempotentStorage;
use Psr\Clock\ClockInterface;

/**
 * Although this class is an application service, it still contains business logic.
 * The business logic is very limited. There's no need for a fully fledged domain model.
 */
final class ChatService
{
    /**
     * @param IdempotentStorage<ChatId> $idempotentChatIdStorage
     */
    public function __construct(
        private readonly ChatGateway $chatGateway,
        private readonly EventStore $eventStore,
        private readonly ClockInterface $clock,
        private readonly IdempotentStorage $idempotentChatIdStorage
    ) {
    }

    public function initiateChat(InitiateChatCommand $initiateChatCommand): string
    {
        $chatId = $this->idempotentChatIdStorage->add(
            $initiateChatCommand->idempotencyKey(),
            $this->chatGateway->nextIdentity()
        );

        try {
            $this->chatGateway->create($chatId, $initiateChatCommand->authors());
            $this->eventStore->append(
                new ChatInitiated($chatId)
            );
        } catch (ChatAlreadyExistsException) {
            // This happens when a command with the same idempotency key is executed more than once.
            // In this case we can safely ignore the exception. However, if we cannot trust our chat id
            // generation to create unique chat ids, we should compare the idempotency key from the command
            // with the idempotency key from the chat. If they are different, we could throw an exception, log
            // or retry with a new idempotency key by appending "-retry".
        }

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

        $writtenAt = $this->clock->now();

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
