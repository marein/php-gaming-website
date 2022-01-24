<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\User\Event;

use Gaming\Common\Domain\DomainEvent;
use Gaming\Identity\Domain\Model\User\UserId;

final class UserSignedUp implements DomainEvent
{
    private string $userId;

    private string $username;

    public function __construct(UserId $userId, string $username)
    {
        $this->userId = $userId->toString();
        $this->username = $username;
    }

    public function aggregateId(): string
    {
        return $this->userId;
    }

    public function username(): string
    {
        return $this->username;
    }

    public function payload(): array
    {
        return [
            'userId' => $this->userId,
            'username' => $this->username
        ];
    }

    public function name(): string
    {
        return 'UserSignedUp';
    }
}
