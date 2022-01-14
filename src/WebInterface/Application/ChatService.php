<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Application;

interface ChatService
{
    /**
     * @return array<string, mixed>
     */
    public function writeMessage(string $chatId, string $authorId, string $message): array;

    /**
     * @return array<string, mixed>
     */
    public function messages(string $chatId, string $authorId, int $offset, int $limit): array;
}
