<?php

declare(strict_types=1);

namespace Gaming\Chat\Application\Query;

final class MessagesQuery
{
    private string $chatId;

    private string $authorId;

    private int $offset;

    private int $limit;

    public function __construct(string $chatId, string $authorId, int $offset, int $limit)
    {
        $this->chatId = $chatId;
        $this->authorId = $authorId;
        $this->offset = $offset;
        $this->limit = $limit;
    }

    public function chatId(): string
    {
        return $this->chatId;
    }

    public function authorId(): string
    {
        return $this->authorId;
    }

    public function offset(): int
    {
        return $this->offset;
    }

    public function limit(): int
    {
        return $this->limit;
    }
}
