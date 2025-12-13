<?php

declare(strict_types=1);

namespace Gaming\Chat\Application\Command;

use Gaming\Common\Bus\Request;

/**
 * @implements Request<string>
 */
final class InitiateChatCommand implements Request
{
    /**
     * @param string[] $authors
     */
    public function __construct(
        public readonly string $idempotencyKey,
        public readonly array $authors
    ) {
    }
}
