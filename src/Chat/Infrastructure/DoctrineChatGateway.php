<?php
declare(strict_types=1);

namespace Gaming\Chat\Infrastructure;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Gaming\Chat\Application\ChatGateway;
use Gaming\Chat\Application\ChatId;
use Gaming\Chat\Application\Exception\ChatNotFoundException;

final class DoctrineChatGateway implements ChatGateway
{
    private const TABLE_CHAT = 'chat';
    private const TABLE_MESSAGE = 'message';

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
    public function create(string $ownerId, array $authors): ChatId
    {
        $chatId = ChatId::generate();

        $this->connection->insert(
            self::TABLE_CHAT,
            [
                'id'      => $chatId->toString(),
                'ownerId' => $ownerId,
                'authors' => $authors
            ],
            ['uuid_binary_ordered_time', 'string', 'json']
        );

        return $chatId;
    }

    /**
     * @inheritdoc
     */
    public function createMessage(
        ChatId $chatId,
        string $authorId,
        string $message,
        DateTimeImmutable $writtenAt
    ): int {
        $this->connection->insert(
            self::TABLE_MESSAGE,
            [
                'chatId'    => $chatId->toString(),
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
    public function messages(ChatId $chatId, string $authorId, int $offset, int $limit): array
    {
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
            ->setParameter('chatId', $chatId->toString(), 'uuid_binary_ordered_time')
            ->setParameter('authorId', $authorId, 'json')
            ->execute()
            ->fetchAll();
    }

    /**
     * @inheritdoc
     */
    public function byId(ChatId $chatId): array
    {
        $chat = $this->connection->createQueryBuilder()
            ->select('BIN_TO_UUID(c.id, 1) as id, c.ownerId, c.authors')
            ->from(self::TABLE_CHAT, 'c')
            ->where('c.id = :id')
            ->setParameter('id', $chatId->toString(), 'uuid_binary_ordered_time')
            ->execute()
            ->fetch();

        if (!$chat) {
            throw new ChatNotFoundException();
        }

        return $chat;
    }
}
