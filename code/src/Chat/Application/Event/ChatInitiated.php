<?php

namespace Gambling\Chat\Application\Event;

use Gambling\Common\Domain\DomainEvent;

final class ChatInitiated implements DomainEvent
{
    /**
     * @var string
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
     * @param string $chatId
     * @param string $ownerId
     */
    public function __construct($chatId, $ownerId)
    {
        $this->chatId = $chatId;
        $this->ownerId = $ownerId;
        $this->occurredOn = new \DateTimeImmutable();
    }

    /**
     * @inheritdoc
     */
    public function name(): string
    {
        return 'chat.chat-initiated';
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
    public function payload(): array
    {
        return [
            'chatId'  => $this->chatId,
            'ownerId' => $this->ownerId
        ];
    }
}
