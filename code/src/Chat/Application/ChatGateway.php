<?php
declare(strict_types=1);

namespace Gambling\Chat\Application;

use Gambling\Chat\Application\Exception\ChatNotFoundException;

interface ChatGateway
{
    /**
     * Create a chat.
     *
     * @param string $ownerId
     * @param array  $authors
     *
     * @return ChatId
     */
    public function create(string $ownerId, array $authors): ChatId;

    /**
     * Create a message.
     *
     * @param ChatId             $chatId
     * @param string             $authorId
     * @param string             $message
     * @param \DateTimeImmutable $writtenAt
     *
     * @return int
     */
    public function createMessage(
        ChatId $chatId,
        string $authorId,
        string $message,
        \DateTimeImmutable $writtenAt
    ): int;

    /**
     * Get chat by id.
     *
     * @param ChatId $chatId
     *
     * @return array
     * @throws ChatNotFoundException
     */
    public function byId(ChatId $chatId): array;

    /**
     * Get messages by chat.
     *
     * @param ChatId $chatId
     * @param string $authorId
     * @param int    $offset
     * @param int    $limit
     *
     * @return array
     */
    public function messages(ChatId $chatId, string $authorId, int $offset, int $limit): array;
}
