<?php

declare(strict_types=1);

namespace Gaming\Chat\Application\Command;

final class InitiateChatCommand
{
    /**
     * @var string[]
     */
    private array $authors;

    /**
     * @param string[] $authors
     */
    public function __construct(array $authors)
    {
        $this->authors = $authors;
    }

    /**
     * @return string[]
     */
    public function authors(): array
    {
        return $this->authors;
    }
}
