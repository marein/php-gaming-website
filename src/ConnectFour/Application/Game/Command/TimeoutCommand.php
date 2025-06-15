<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Command;

use Gaming\Common\Bus\Request;

/**
 * @implements Request<void>
 */
final class TimeoutCommand implements Request
{
    public function __construct(
        public readonly string $gameId
    ) {
    }
}
