<?php

declare(strict_types=1);

namespace Gaming\Chat\Application\Event;

use DateTimeImmutable;
use Gaming\Chat\Application\ChatId;
use Gaming\Common\Clock\Clock;
use Gaming\Common\Domain\DomainEvent;

final class ChatInitiated implements DomainEvent
{
    private ChatId $chatId;

    private string $ownerId;

    private DateTimeImmutable $occurredOn;

    public function __construct(ChatId $chatId, string $ownerId)
    {
        $this->chatId = $chatId;
        $this->ownerId = $ownerId;
        $this->occurredOn = Clock::instance()->now();
    }

    public function name(): string
    {
        return 'ChatInitiated';
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function aggregateId(): string
    {
        return $this->chatId->toString();
    }

    public function payload(): array
    {
        return [
            'chatId' => $this->chatId->toString(),
            'ownerId' => $this->ownerId
        ];
    }
}
