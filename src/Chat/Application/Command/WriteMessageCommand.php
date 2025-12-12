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
        public readonly string $chatId,
        public readonly string $authorId,
        public readonly string $message,
        public readonly ?string $idempotencyKey = null
    ) {
    }
}
