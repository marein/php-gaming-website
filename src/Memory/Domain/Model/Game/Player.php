<?php

declare(strict_types=1);

namespace Gaming\Memory\Domain\Model\Game;

final class Player
{
    /**
     * @var string
     */
    private string $id;

    /**
     * Player constructor.
     *
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return $this->id;
    }
}
