<?php

declare(strict_types=1);

namespace Gaming\Chat\Application\Command;

final class InitiateChatCommand
{
    /**
     * @var string
     */
    private string $ownerId;

    /**
     * @var string[]
     */
    private array $authors;

    /**
     * InitiateChatCommand constructor.
     *
     * @param string $ownerId
     * @param string[] $authors
     */
    public function __construct(string $ownerId, array $authors)
    {
        $this->ownerId = $ownerId;
        $this->authors = $authors;
    }

    /**
     * @return string
     */
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
