<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Event;

use Gaming\Common\Domain\DomainEvent;
use Gaming\ConnectFour\Domain\Game\GameId;

final class ChatAssigned implements DomainEvent
{
    private string $gameId;

    private string $chatId;

    public function __construct(GameId $gameId, string $chatId)
    {
        $this->gameId = $gameId->toString();
        $this->chatId = $chatId;
    }

    public function aggregateId(): string
    {
        return $this->gameId;
    }

    public function chatId(): string
    {
        return $this->chatId;
    }

    public function payload(): array
    {
        return [
            'gameId' => $this->gameId,
            'chatId' => $this->chatId
        ];
    }

    public function name(): string
    {
        return 'ChatAssigned';
    }
}
