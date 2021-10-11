<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\User\Event;

use DateTimeImmutable;
use Gaming\Common\Clock\Clock;
use Gaming\Common\Domain\DomainEvent;
use Gaming\Identity\Domain\Model\User\UserId;

final class UserArrived implements DomainEvent
{
    private UserId $userId;

    private DateTimeImmutable $occurredOn;

    public function __construct(UserId $userId)
    {
        $this->userId = $userId;
        $this->occurredOn = Clock::instance()->now();
    }

    public function aggregateId(): string
    {
        return $this->userId->toString();
    }

    public function payload(): array
    {
        return [
            'userId' => $this->userId->toString()
        ];
    }

    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function name(): string
    {
        return 'UserArrived';
    }
}
