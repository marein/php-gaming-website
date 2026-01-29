<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Application\Challenge\Withdraw;

use Gaming\Common\Bus\Request;

/**
 * @implements Request<void>
 */
final class WithdrawRequest implements Request
{
    public function __construct(
        public readonly string $challengeId,
        public readonly string $playerId
    ) {
    }
}
