<?php

namespace Gambling\Chat\Application\Event;

use Gambling\Chat\Application\ChatId;
use Gambling\Common\Clock\Clock;
use Gambling\Common\Domain\DomainEvent;

final class ChatInitiated implements DomainEvent
{
    /**
     * @var ChatId
     */
    private $chatId;

    /**
     * @var string
     */
    private $ownerId;

    /**
     * @var \DateTimeImmutable
     */
    private $occurredOn;

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
    public function occurredOn(): \DateTimeImmutable
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
