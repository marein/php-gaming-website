<?php

namespace Gambling\WebInterface\Application;

interface ChatService
{
    /**
     * Write a message.
     *
     * @param string $chatId
     * @param string $authorId
     * @param string $message
     *
     * @return array
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
     * @return array
     */
    public function messages(string $chatId, string $authorId, int $offset, int $limit): array;
}
