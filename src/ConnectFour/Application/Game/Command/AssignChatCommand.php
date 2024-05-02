<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Command;

use Gaming\Common\Bus\Request;

/**
 * @implements Request<void>
 */
final class AssignChatCommand implements Request
{
    public function __construct(
        private readonly string $gameId,
        private readonly string $chatId
    ) {
    }

    public function gameId(): string
    {
        return $this->gameId;
    }

    public function chatId(): string
    {
        return $this->chatId;
    }
}
