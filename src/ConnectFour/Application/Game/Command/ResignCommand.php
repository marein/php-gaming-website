<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Command;

use Gaming\Common\Bus\Request;

/**
 * @implements Request<void>
 */
final class ResignCommand implements Request
{
    public function __construct(
        private readonly string $gameId,
        private readonly string $playerId
    ) {
    }

    public function gameId(): string
    {
        return $this->gameId;
    }

    public function playerId(): string
    {
        return $this->playerId;
    }
}
