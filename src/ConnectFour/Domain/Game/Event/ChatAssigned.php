<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Event;

use DateTimeImmutable;
use Gaming\Common\Clock\Clock;
use Gaming\Common\Domain\DomainEvent;
use Gaming\ConnectFour\Domain\Game\GameId;

final class ChatAssigned implements DomainEvent
{
    private string $gameId;

    private string $chatId;

    private DateTimeImmutable $occurredOn;

    public function __construct(GameId $gameId, string $chatId)
    {
        $this->gameId = $gameId->toString();
        $this->chatId = $chatId;
        $this->occurredOn = Clock::instance()->now();
    }

    public function aggregateId(): string
    {
        return $this->gameId;
    }

    public function payload(): array
    {
        return [
            'gameId' => $this->gameId,
            'chatId' => $this->chatId
        ];
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function name(): string
    {
        return 'ChatAssigned';
    }
}
