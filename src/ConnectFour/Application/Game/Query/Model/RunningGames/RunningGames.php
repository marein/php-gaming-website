<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\Model\RunningGames;

final class RunningGames
{
    private int $count;

    public function __construct(int $count)
    {
        $this->count = $count;
    }

    public function count(): int
    {
        return $this->count;
    }
}
