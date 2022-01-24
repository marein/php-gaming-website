<?php

declare(strict_types=1);

namespace Gaming\Chat\Application\Event;

use DateTimeImmutable;
use DateTimeInterface;
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

    public function name(): string
    {
        return 'MessageWritten';
    }

    public function aggregateId(): string
    {
        return $this->chatId;
    }

    public function payload(): array
    {
        return [
            'chatId' => $this->chatId,
            'messageId' => $this->messageId,
            'ownerId' => $this->ownerId,
            'authorId' => $this->authorId,
            'message' => $this->message,
            'writtenAt' => $this->writtenAt->format(DateTimeInterface::ATOM)
        ];
    }
}
