<?php

declare(strict_types=1);

namespace Gaming\Chat\Application\Command;

use Gaming\Common\Bus\Request;

/**
 * @implements Request<void>
 */
final class WriteMessageCommand implements Request
{
    public function __construct(
        private readonly string $chatId,
        private readonly string $authorId,
        private readonly string $message
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

    public function message(): string
    {
        return $this->message;
    }
}
