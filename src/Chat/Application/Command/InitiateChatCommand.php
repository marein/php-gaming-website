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
        private readonly string $idempotencyKey,
        private readonly array $authors
    ) {
    }

    public function idempotencyKey(): string
    {
        return $this->idempotencyKey;
    }

    /**
     * @return string[]
     */
    public function authors(): array
    {
        return $this->authors;
    }
}
