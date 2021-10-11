<?php

declare(strict_types=1);

namespace Gaming\Chat\Application\Command;

final class WriteMessageCommand
{
    private string $chatId;

    private string $authorId;

    private string $message;

    public function __construct(string $chatId, string $authorId, string $message)
    {
        $this->chatId = $chatId;
        $this->authorId = $authorId;
        $this->message = $message;
    }

    public function chatId(): string
    {
        return $this->chatId;
    }

    public function authorId(): string
    {
        return $this->authorId;
    }

    public function message(): string
    {
        return $this->message;
    }
}
