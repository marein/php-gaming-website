<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Command;

final class OpenCommand
{
    /**
     * @var string
     */
    private string $playerId;

    /**
     * OpenCommand constructor.
     *
     * @param string $playerId
     */
    public function __construct(string $playerId)
    {
        $this->playerId = $playerId;
    }

    /**
     * @return string
     */
    public function playerId(): string
    {
        return $this->playerId;
    }
}
