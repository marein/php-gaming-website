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
        $chatId = Uuid::uuid1();

        $this->connection->insert(
            self::TABLE_CHAT,
            [
                'id'      => $chatId,
                'ownerId' => $ownerId,
                'authors' => $authors
            ],
            ['uuid_binary_ordered_time', 'string', 'json']
        );

        return $chatId->toString();
    }

    /**
     * @inheritdoc
     */
    public function createMessage(
        string $chatId,
        string $authorId,
        string $message,
        \DateTimeImmutable $writtenAt
    ): int {
        $this->connection->insert(
            self::TABLE_MESSAGE,
            [
                'chatId'    => Uuid::fromString($chatId),
                'authorId'  => $authorId,
                'message'   => $message,
                'writtenAt' => $writtenAt
            ],
            ['uuid_binary_ordered_time', 'string', 'string', 'datetime_immutable']
        );

        return (int)$this->connection->lastInsertId();
    }

    /**
     * @inheritdoc
     */
    public function messages(string $chatId, string $authorId, int $offset, int $limit): array
    {
        // If authors are assigned to the chat, only those authors can read messages.
        // Directly format the date as \DateTime::ATOM, since we know that our server saves dates as UTC.
        return $this->connection->createQueryBuilder()
            ->select('
                m.id as messageId,
                m.authorId,
                m.message,
                DATE_FORMAT(m.writtenAt, "%Y-%m-%dT%T+00:00") as writtenAt
            ')
            ->from(self::TABLE_CHAT, 'c')
            ->leftJoin('c', self::TABLE_MESSAGE, 'm', 'c.id = m.chatId')
            ->where('m.chatId = :chatId')
            ->andWhere(
                'JSON_LENGTH(c.authors) = 0 OR JSON_CONTAINS(c.authors, :authorId) > 0'
            )
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->setParameter('chatId', $chatId, 'uuid_binary_ordered_time')
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
            ->select('BIN_TO_UUID(c.id, 1) as id, c.ownerId, c.authors')
            ->from(self::TABLE_CHAT, 'c')
            ->where('c.id = :id')
            ->setParameter('id', $chatId, 'uuid_binary_ordered_time')
            ->execute()
            ->fetch();

        if (!$chat) {
            throw new ChatNotFoundException();
        }

        return $chat;
    }
}
