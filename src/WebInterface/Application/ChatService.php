<?php
declare(strict_types=1);

namespace Gaming\WebInterface\Application;

interface ChatService
{
    /**
     * Write a message.
     *
     * @param string $chatId
     * @param string $authorId
     * @param string $message
     *
     * @return array<string, mixed>
     */
    public function writeMessage(string $chatId, string $authorId, string $message): array;

    /**
     * Get the messages in the chat.
     *
     * @param string $chatId
     * @param string $authorId
     * @param int    $offset
     * @param int    $limit
     *
     * @return array<string, mixed>
     */
    public function messages(string $chatId, string $authorId, int $offset, int $limit): array;
}
