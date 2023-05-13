<?php

declare(strict_types=1);

namespace Gaming\Chat\Application\Command;

final class InitiateChatCommand
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
