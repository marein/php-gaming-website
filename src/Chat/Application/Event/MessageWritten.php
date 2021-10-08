<?php

declare(strict_types=1);

namespace Gaming\Chat\Application\Event;

use DateTime;
use DateTimeImmutable;
use Gaming\Chat\Application\ChatId;
use Gaming\Common\Clock\Clock;
use Gaming\Common\Domain\DomainEvent;

final class MessageWritten implements DomainEvent
{
    private ChatId $chatId;

    private int $messageId;

    private string $ownerId;

    private string $authorId;

    private string $message;

    private DateTimeImmutable $writtenAt;

    private DateTimeImmutable $occurredOn;

    public function __construct(
        ChatId $chatId,
        int $messageId,
        string $ownerId,
        string $authorId,
        string $message,
        DateTimeImmutable $writtenAt
    ) {
        $this->chatId = $chatId;
        $this->messageId = $messageId;
        $this->ownerId = $ownerId;
        $this->authorId = $authorId;
        $this->message = $message;
        $this->writtenAt = $writtenAt;
        $this->occurredOn = Clock::instance()->now();
    }

    public function name(): string
    {
        return 'MessageWritten';
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
            'messageId' => $this->messageId,
            'ownerId' => $this->ownerId,
            'authorId' => $this->authorId,
            'message' => $this->message,
            'writtenAt' => $this->writtenAt->format(DateTime::ATOM)
        ];
    }
}
