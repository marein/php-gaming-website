<?php
declare(strict_types=1);

namespace Gambling\ConnectFour\Application\Game\Command;

final class OpenCommand
{
    /**
     * @var string
     */
    private $playerId;

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
