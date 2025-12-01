<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\User\Event;

use Gaming\Common\Domain\DomainEvent;
use Gaming\Identity\Domain\Model\Account\AccountId;

final class UserArrived implements DomainEvent
{
    private string $userId;

    public function __construct(AccountId $userId)
    {
        $this->userId = $userId->toString();
    }

    public function aggregateId(): string
    {
        return $this->userId;
    }
}
