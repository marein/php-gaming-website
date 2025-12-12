<?php

declare(strict_types=1);

namespace Gaming\Chat\Application;

use DateTimeImmutable;
use Gaming\Chat\Application\Exception\ChatAlreadyExistsException;
use Gaming\Chat\Application\Exception\ChatNotFoundException;
use Gaming\Chat\Application\Exception\MessageAlreadyWrittenException;

interface ChatGateway
{
    public function nextIdentity(): ChatId;

    /**
     * @param string[] $authors
     *
     * @throws ChatAlreadyExistsException
     */
    public function create(ChatId $chatId, array $authors): void;

    /**
     * @throws MessageAlreadyWrittenException
     */
    public function createMessage(
        ChatId $chatId,
        string $authorId,
        string $message,
        DateTimeImmutable $writtenAt,
        ?string $idempotencyKey = null
    ): int;

    /**
     * @return array<string, mixed>
     * @throws ChatNotFoundException
     */
    public function byId(ChatId $chatId): array;

    /**
     * If authors are assigned to the chat, only those authors can read messages.
     *
     * @return array<int, array<string, mixed>>
     */
    public function messages(ChatId $chatId, string $authorId, int $offset, int $limit): array;
}
