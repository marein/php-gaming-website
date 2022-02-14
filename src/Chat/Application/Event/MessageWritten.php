<?php

declare(strict_types=1);

namespace Gaming\Chat\Application\Event;

use DateTimeImmutable;
use Gaming\Chat\Application\ChatId;
use Gaming\Common\Domain\DomainEvent;

final class MessageWritten implements DomainEvent
{
    private string $chatId;

    private int $messageId;

    private string $ownerId;

    private string $authorId;

    private string $message;

    private DateTimeImmutable $writtenAt;

    public function __construct(
        ChatId $chatId,
        int $messageId,
        string $ownerId,
        string $authorId,
        string $message,
        DateTimeImmutable $writtenAt
    ) {
        $this->chatId = $chatId->toString();
        $this->messageId = $messageId;
        $this->ownerId = $ownerId;
        $this->authorId = $authorId;
        $this->message = $message;
        $this->writtenAt = $writtenAt;
    }

    public function aggregateId(): string
    {
        return $this->chatId;
    }

    public function messageId(): int
    {
        return $this->messageId;
    }

    public function ownerId(): string
    {
        return $this->ownerId;
    }

    public function authorId(): string
    {
        return $this->authorId;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function writtenAt(): DateTimeImmutable
    {
        return $this->writtenAt;
    }
}
