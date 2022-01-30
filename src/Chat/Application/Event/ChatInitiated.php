<?php

declare(strict_types=1);

namespace Gaming\Chat\Application\Event;

use Gaming\Chat\Application\ChatId;
use Gaming\Common\Domain\DomainEvent;

final class ChatInitiated implements DomainEvent
{
    private string $chatId;

    public function __construct(ChatId $chatId)
    {
        $this->chatId = $chatId->toString();
    }

    public function aggregateId(): string
    {
        return $this->chatId;
    }
}
