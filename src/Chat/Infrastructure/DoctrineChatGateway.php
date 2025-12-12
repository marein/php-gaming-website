<?php

declare(strict_types=1);

namespace Gaming\Chat\Infrastructure;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Gaming\Chat\Application\ChatGateway;
use Gaming\Chat\Application\ChatId;
use Gaming\Chat\Application\Exception\ChatAlreadyExistsException;
use Gaming\Chat\Application\Exception\ChatNotFoundException;
use Gaming\Chat\Application\Exception\MessageAlreadyWrittenException;

final class DoctrineChatGateway implements ChatGateway
{
    public function __construct(
        private readonly Connection $connection,
        private readonly string $chatTableName,
        private readonly string $messageTableName,
        private readonly string $idempotencySecret
    ) {
    }

    public function nextIdentity(): ChatId
    {
        return ChatId::generate();
    }

    public function create(ChatId $chatId, array $authors): void
    {
        try {
            $this->connection->insert(
                $this->chatTableName,
                [
                    'id' => $chatId->toString(),
                    'authors' => $authors
                ],
                ['uuid', 'json']
            );
        } catch (UniqueConstraintViolationException) {
            throw new ChatAlreadyExistsException();
        }
    }

    public function createMessage(
        ChatId $chatId,
        string $authorId,
        string $message,
        DateTimeImmutable $writtenAt,
        ?string $idempotencyKey = null
    ): int {
        try {
            $this->connection->insert(
                $this->messageTableName,
                [
                    'chatId' => $chatId->toString(),
                    'authorId' => $authorId,
                    'message' => $message,
                    'writtenAt' => $writtenAt,
                    'idempotencyKey' => $idempotencyKey !== null
                        ? sodium_crypto_generichash($chatId . $authorId . $idempotencyKey, $this->idempotencySecret, 16)
                        : null
                ],
                ['uuid', 'uuid', 'string', 'datetime_immutable', 'binary']
            );

            return (int)$this->connection->lastInsertId();
        } catch (UniqueConstraintViolationException) {
            throw new MessageAlreadyWrittenException();
        }
    }

    public function messages(ChatId $chatId, string $authorId, int $offset, int $limit): array
    {
        // Directly format the date as \DateTime::ATOM, since we know that our server saves dates as UTC.
        return $this->connection->createQueryBuilder()
            ->select('
                m.id as messageId,
                BIN_TO_UUID(m.authorId) as authorId,
                m.message,
                DATE_FORMAT(m.writtenAt, "%Y-%m-%dT%T+00:00") as writtenAt
            ')
            ->from($this->chatTableName, 'c')
            ->leftJoin('c', $this->messageTableName, 'm', 'c.id = m.chatId')
            ->where('m.chatId = :chatId')
            ->andWhere(
                'JSON_LENGTH(c.authors) = 0 OR JSON_CONTAINS(c.authors, :authorId) > 0'
            )
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->setParameter('chatId', $chatId->toString(), 'uuid')
            ->setParameter('authorId', $authorId, 'json')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    public function byId(ChatId $chatId): array
    {
        $chat = $this->connection->createQueryBuilder()
            ->select('BIN_TO_UUID(c.id) as id, c.authors')
            ->from($this->chatTableName, 'c')
            ->where('c.id = :id')
            ->setParameter('id', $chatId->toString(), 'uuid')
            ->executeQuery()
            ->fetchAssociative();

        if (!$chat) {
            throw new ChatNotFoundException();
        }

        return $chat;
    }
}
