<?php

declare(strict_types=1);

namespace Gaming\Chat\Application\Command;

final class InitiateChatCommand
{
    private string $ownerId;

    /**
     * @var string[]
     */
    private array $authors;

    /**
     * @param string[] $authors
     */
    public function __construct(string $ownerId, array $authors)
    {
        $this->ownerId = $ownerId;
        $this->authors = $authors;
    }

    public function ownerId(): string
    {
        return $this->ownerId;
    }

    /**
     * @return string[]
     */
    public function authors(): array
    {
        return $this->authors;
    }
}
