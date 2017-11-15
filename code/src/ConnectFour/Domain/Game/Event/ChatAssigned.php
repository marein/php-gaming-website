<?php

namespace Gambling\ConnectFour\Domain\Game\Event;

use Gambling\Common\Domain\DomainEvent;
use Gambling\ConnectFour\Domain\Game\GameId;

final class ChatAssigned implements DomainEvent
{
    /**
     * @var GameId
     */
    private $gameId;

    /**
     * @var string
     */
    private $chatId;

    /**
     * @var \DateTimeImmutable
     */
    private $occurredOn;

    /**
     * ChatAssigned constructor.
     *
     * @param GameId $gameId
     * @param string $chatId
     */
    public function __construct(GameId $gameId, string $chatId)
    {
        $this->gameId = $gameId;
        $this->chatId = $chatId;
        $this->occurredOn = new \DateTimeImmutable();
    }

    /**
     * @inheritdoc
     */
    public function payload(): array
    {
        return [
            'gameId' => $this->gameId->toString(),
            'chatId' => $this->chatId
        ];
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
    public function name(): string
    {
        return 'connect-four.chat-assigned';
    }
}
