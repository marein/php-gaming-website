<?php
declare(strict_types=1);

namespace Gaming\Chat\Application;

use DateTimeImmutable;
use Gaming\Chat\Application\Exception\ChatNotFoundException;

interface ChatGateway
{
    /**
     * Create a chat.
     *
     * @param string   $ownerId
     * @param string[] $authors
     *
     * @return ChatId
     */
    public function create(string $ownerId, array $authors): ChatId;

    /**
     * Create a message.
     *
     * @param ChatId            $chatId
     * @param string            $authorId
     * @param string            $message
     * @param DateTimeImmutable $writtenAt
     *
     * @return int
     */
    public function createMessage(
        ChatId $chatId,
        string $authorId,
        string $message,
        DateTimeImmutable $writtenAt
    ): int;

    /**
     * Get chat by id.
     *
     * @param ChatId $chatId
     *
     * @return array<string, mixed>
     * @throws ChatNotFoundException
     */
    public function byId(ChatId $chatId): array;

    /**
     * Get messages by chat.
     *
     * If authors are assigned to the chat, only those authors can read messages.
     *
     * @param ChatId $chatId
     * @param string $authorId
     * @param int    $offset
     * @param int    $limit
     *
     * @return array<int, array<string, mixed>>
     */
    public function messages(ChatId $chatId, string $authorId, int $offset, int $limit): array;
}
