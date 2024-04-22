<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Command;

use Gaming\Common\Bus\Request;

/**
 * @implements Request<string>
 */
final class OpenCommand implements Request
{
    public function __construct(
        private readonly string $playerId
    ) {
    }

    public function playerId(): string
    {
        return $this->playerId;
    }
}
