<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Command;

final class AssignChatCommand
{
    private string $gameId;

    private string $chatId;

    public function __construct(string $gameId, string $chatId)
    {
        $this->gameId = $gameId;
        $this->chatId = $chatId;
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
