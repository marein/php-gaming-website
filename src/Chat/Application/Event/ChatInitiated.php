<?php
declare(strict_types=1);

namespace Gaming\Chat\Application\Event;

use DateTimeImmutable;
use Gaming\Chat\Application\ChatId;
use Gaming\Common\Clock\Clock;
use Gaming\Common\Domain\DomainEvent;

final class ChatInitiated implements DomainEvent
{
    /**
     * @var ChatId
     */
    private ChatId $chatId;

    /**
     * @var string
     */
    private string $ownerId;

    /**
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $occurredOn;

    /**
     * ChatInitiated constructor.
     *
     * @param ChatId $chatId
     * @param string $ownerId
     */
    public function __construct(ChatId $chatId, string $ownerId)
    {
        $this->chatId = $chatId;
        $this->ownerId = $ownerId;
        $this->occurredOn = Clock::instance()->now();
    }

    /**
     * @inheritdoc
     */
    public function name(): string
    {
        return 'ChatInitiated';
    }

    /**
     * @inheritdoc
     */
    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    /**
     * @inheritdoc
     */
    public function aggregateId(): string
    {
        return $this->chatId->toString();
    }

    /**
     * @inheritdoc
     */
    public function payload(): array
    {
        return [
            'chatId'  => $this->chatId->toString(),
            'ownerId' => $this->ownerId
        ];
    }
}
