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
        public readonly string $playerId,
        public readonly int $width,
        public readonly int $height,
        public readonly int $stone
    ) {
    }
}
