<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\User\Event;

use Gaming\Common\Domain\DomainEvent;
use Gaming\Identity\Domain\Model\User\UserId;

final class UserSignedUp implements DomainEvent
{
    public readonly string $userId;

    public function __construct(
        UserId $userId,
        public readonly string $email,
        public readonly string $username
    ) {
        $this->userId = $userId->toString();
    }

    public function aggregateId(): string
    {
        return $this->userId;
    }
}
