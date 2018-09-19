<?php
declare(strict_types=1);

namespace Gambling\Chat\Application;

use Gambling\Chat\Application\Event\ChatInitiated;
use Gambling\Chat\Application\Event\MessageWritten;
use Gambling\Chat\Application\Exception\AuthorNotAllowedException;
use Gambling\Chat\Application\Exception\ChatNotFoundException;
use Gambling\Chat\Application\Exception\EmptyMessageException;
use Gambling\Common\Application\ApplicationLifeCycle;
use Gambling\Common\Clock\Clock;
use Gambling\Common\EventStore\EventStore;

/**
 * Although this class is an application service, it still contains business logic.
 * The business logic is very limited. There's no need for a fully fledged domain model.
 */
final class ChatService
{
    /**
     * @var ApplicationLifeCycle
     */
    private $applicationLifeCycle;

    /**
     * @var ChatGateway
     */
    private $chatGateway;

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * ChatService constructor.
     *
     * @param ApplicationLifeCycle $applicationLifeCycle
     * @param ChatGateway          $chatGateway
     * @param EventStore           $eventStore
     */
    public function __construct(
        ApplicationLifeCycle $applicationLifeCycle,
        ChatGateway $chatGateway,
        EventStore $eventStore
    ) {
        $this->applicationLifeCycle = $applicationLifeCycle;
        $this->chatGateway = $chatGateway;
        $this->eventStore = $eventStore;
    }

    /**
     * Initiate a new chat.
     *
     * @param string $ownerId
     * @param array  $authors
     *
     * @return string
     */
    public function initiateChat(string $ownerId, array $authors): string
    {
        return $this->applicationLifeCycle->run(
            function () use ($ownerId, $authors) {
                $chatId = $this->chatGateway->create($ownerId, $authors);

                $this->eventStore->append(
                    new ChatInitiated($chatId, $ownerId)
                );

                return $chatId->toString();
            }
        );
    }

    /**
     * Write a message to the chat.
     *
     * @param string $chatId
     * @param string $authorId
     * @param string $message
     *
     * @throws ChatNotFoundException
     * @throws AuthorNotAllowedException
     * @throws EmptyMessageException
     */
    public function writeMessage(string $chatId, string $authorId, string $message): void
    {
        $chatId = ChatId::fromString($chatId);
        $message = trim(substr($message, 0, 140));

        if ($message === '') {
            throw new EmptyMessageException();
        }

        $chat = $this->chatGateway->byId($chatId);
        $authors = json_decode($chat['authors'], true);

        // If authors are assigned to the chat, only those authors can write messages.
        if (!empty($authors) && !in_array($authorId, $authors)) {
            throw new AuthorNotAllowedException();
        }

        $ownerId = $chat['ownerId'];
        $writtenAt = Clock::instance()->now();

        $this->applicationLifeCycle->run(
            function () use ($chatId, $ownerId, $authorId, $message, $writtenAt) {
                $messageId = $this->chatGateway->createMessage($chatId, $authorId, $message, $writtenAt);

                $this->eventStore->append(
                    new MessageWritten($chatId, $messageId, $ownerId, $authorId, $message, $writtenAt)
                );
            }
        );
    }

    /**
     * Get messages by chat.
     *
     * @param string $chatId
     * @param string $authorId
     * @param int    $offset
     * @param int    $limit
     *
     * @return array
     */
    public function messages(string $chatId, string $authorId, int $offset, int $limit): array
    {
        return $this->chatGateway->messages(
            ChatId::fromString($chatId),
            $authorId,
            $offset,
            $limit
        );
    }
}
