<?php

declare(strict_types=1);

namespace Gaming\Chat\Application\Event;

use Gaming\Chat\Application\ChatId;
use Gaming\Common\Domain\DomainEvent;

final class ChatInitiated implements DomainEvent
{
    private string $chatId;

    private string $ownerId;

    public function __construct(ChatId $chatId, string $ownerId)
    {
        $this->chatId = $chatId->toString();
        $this->ownerId = $ownerId;
    }

    public function name(): string
    {
        return 'ChatInitiated';
    }

    public function aggregateId(): string
    {
        return $this->chatId;
    }

    public function ownerId(): string
    {
        return $this->ownerId;
    }

    public function payload(): array
    {
        return [
            'chatId' => $this->chatId,
            'ownerId' => $this->ownerId
        ];
    }
}
