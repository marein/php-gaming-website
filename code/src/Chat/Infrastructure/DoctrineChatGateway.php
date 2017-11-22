<?php

namespace Gambling\Chat\Infrastructure;

use Doctrine\DBAL\Connection;
use Gambling\Chat\Application\ChatGateway;
use Gambling\Chat\Application\Exception\ChatNotFoundException;
use Ramsey\Uuid\Uuid;

final class DoctrineChatGateway implements ChatGateway
{
    const TABLE_CHAT = 'chat';
    const TABLE_MESSAGE = 'message';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * DoctrineChatGateway constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @inheritdoc
     */
    public function create(string $ownerId, array $authors): string
    {
        $chatId = Uuid::uuid4();

        $this->connection->insert(
            self::TABLE_CHAT,
            [
                'id'      => $chatId,
                'ownerId' => $ownerId,
                'authors' => $authors
            ],
            ['string', 'string', 'json']
        );

        return $chatId;
    }

    /**
     * @inheritdoc
     */
    public function createMessage(string $chatId, string $authorId, string $message): int
    {
        $this->connection->insert(
            self::TABLE_MESSAGE,
            [
                'chatId'   => $chatId,
                'authorId' => $authorId,
                'message'  => $message
            ],
            ['string', 'string', 'string']
        );

        return (int)$this->connection->lastInsertId();
    }

    /**
     * @inheritdoc
     */
    public function messages(string $chatId, string $authorId, int $offset, int $limit): array
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
     * @inheritdoc
     */
    public function byId(string $chatId): array
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
