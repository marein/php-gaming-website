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

        // Our only business rule. If authors assigned to the chat, only these authors can write a message.
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
                        'id'       => $this->connection->lastInsertId(),
                        'chatId'   => $messageData['chatId'],
                        'ownerId'  => $chat['ownerId'],
                        'authorId' => $messageData['authorId'],
                        'message'  => $messageData['message']
                    ]
                )
            );
        });
    }

    /**
     * Get messages by chat.
     *
     * @param string $chatId
     * @param int    $offset
     * @param int    $limit
     *
     * @return array
     */
    public function messagesByChat(string $chatId, int $offset, int $limit): array
    {
        return $this->connection->createQueryBuilder()
            ->select('id, authorId, message')
            ->from(self::TABLE_MESSAGE, 'm')
            ->where('m.chatId = :chatId')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->setParameter('chatId', $chatId)
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
