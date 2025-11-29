<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\Bot\Event;

use Gaming\Common\Domain\DomainEvent;
use Gaming\Identity\Domain\Model\Account\AccountId;

final class BotRegistered implements DomainEvent
{
    public readonly string $botId;

    public function __construct(
        AccountId $userId,
        public readonly string $username
    ) {
        $this->botId = $userId->toString();
    }

    public function aggregateId(): string
    {
        return $this->botId;
    }
}
