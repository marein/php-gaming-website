<?php

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
     * @return string
     */
    public function create(string $ownerId, array $authors): string;

    /**
     * Create a message.
     *
     * @param string             $chatId
     * @param string             $authorId
     * @param string             $message
     * @param \DateTimeImmutable $writtenAt
     *
     * @return int
     */
    public function createMessage(
        string $chatId,
        string $authorId,
        string $message,
        \DateTimeImmutable $writtenAt
    ): int;

    /**
     * Get chat by id.
     *
     * @param string $chatId
     *
     * @return array
     * @throws ChatNotFoundException
     */
    public function byId(string $chatId): array;

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
    public function messages(string $chatId, string $authorId, int $offset, int $limit): array;
}
