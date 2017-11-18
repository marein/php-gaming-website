<?php

namespace Gambling\Chat\Model;

use Doctrine\DBAL\Connection;
use Gambling\Chat\Model\Exception\AuthorNotAllowedException;
use Gambling\Chat\Model\Exception\ChatNotFoundException;
use Gambling\Chat\Model\Exception\EmptyMessageException;
use Gambling\Common\EventStore\EventStore;
use Ramsey\Uuid\Uuid;

final class ChatGateway
{
    const TABLE_CHAT = 'chat';
    const TABLE_MESSAGE = 'message';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * ChatGateway constructor.
     *
     * @param Connection $connection
     * @param EventStore $eventStore
     */
    public function __construct(Connection $connection, EventStore $eventStore)
    {
        $this->connection = $connection;
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
        $chatId = Uuid::uuid4();

        $chatData = [
            'id'      => $chatId,
            'ownerId' => $ownerId,
            'authors' => $authors
        ];

        $this->connection->transactional(function () use ($chatData) {
            $this->connection->insert(
                self::TABLE_CHAT,
                $chatData,
                [
                    'string',
                    'string',
                    'json'
                ]
            );
            $this->eventStore->append(
                new GenericDomainEvent(
                    'chat.chat-initiated',
                    [
                        'chatId'  => $chatData['id'],
                        'ownerId' => $chatData['ownerId']
                    ]
                )
            );
        });

        return $chatId;
    }

    /**
     * Write a message to the chat.
     *
     * @param string $chatId
     * @param string $authorId
     * @param string $message
     *
     * @throws AuthorNotAllowedException
     * @throws EmptyMessageException
     */
    public function writeMessage(string $chatId, string $authorId, string $message): void
    {
        $chat = $this->chatById($chatId);
        $authors = json_decode($chat['authors'], true);

        // If authors are assigned to the chat, only those authors can write messages.
        if (!empty($authors) && !in_array($authorId, $authors)) {
            throw new AuthorNotAllowedException();
        }

        $message = trim(substr($message, 0, 140));

        if ($message === '') {
            throw new EmptyMessageException();
        }

        $messageData = [
            'chatId'   => $chatId,
            'authorId' => $authorId,
            'message'  => $message
        ];

        $this->connection->transactional(function () use ($messageData, $chat) {
            $this->connection->insert(
                self::TABLE_MESSAGE,
                $messageData,
                [
                    'string',
                    'string',
                    'string'
                ]
            );
            $this->eventStore->append(
                new GenericDomainEvent(
                    'chat.message-written',
                    [
                        'messageId' => $this->connection->lastInsertId(),
                        'chatId'    => $messageData['chatId'],
                        'ownerId'   => $chat['ownerId'],
                        'authorId'  => $messageData['authorId'],
                        'message'   => $messageData['message']
                    ]
                )
            );
        });
    }

    /**
     * Get messages by chat.
     *
     * @param string $chatId
     * @param string $authorId Id of the author who wants to display the messages.
     * @param int    $offset
     * @param int    $limit
     *
     * @return array
     */
    public function messagesByChat(string $chatId, string $authorId, int $offset, int $limit): array
    {
        // If authors are assigned to the chat, only those authors can read messages.
        return $this->connection->createQueryBuilder()
            ->select('m.id as messageId, m.authorId, m.message')
            ->from(self::TABLE_CHAT, 'c')
            ->leftJoin('c', self::TABLE_MESSAGE, 'm', 'c.id = m.chatId')
            ->where('m.chatId = :chatId')
            ->andWhere(
                'JSON_LENGTH(c.authors) = 0 OR JSON_CONTAINS(c.authors, :authorId) > 0'
            )
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->setParameter('chatId', $chatId)
            ->setParameter('authorId', $authorId, 'json')
            ->execute()
            ->fetchAll();
    }

    /**
     * Get chat by id.
     *
     * @param string $chatId
     *
     * @return array
     * @throws ChatNotFoundException
     */
    private function chatById(string $chatId): array
    {
        $chat = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE_CHAT, 'c')
            ->where('c.id = :id')
            ->setParameter('id', $chatId)
            ->execute()
            ->fetch();

        if (!$chat) {
            throw new ChatNotFoundException();
        }

        return $chat;
    }
}
