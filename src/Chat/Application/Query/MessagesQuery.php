<?php

declare(strict_types=1);

namespace Gaming\Chat\Application\Query;

use Gaming\Common\Bus\Request;

/**
 * @implements Request<array{messageId: string, authorId: string, message: string, writtenAt: string}[]>
 */
final class MessagesQuery implements Request
{
    public function __construct(
        private readonly string $chatId,
        private readonly string $authorId,
        private readonly int $offset,
        private readonly int $limit
    ) {
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
