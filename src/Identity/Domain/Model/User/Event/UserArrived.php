<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\User\Event;

use DateTimeImmutable;
use Gaming\Common\Clock\Clock;
use Gaming\Common\Domain\DomainEvent;
use Gaming\Identity\Domain\Model\User\UserId;

final class UserArrived implements DomainEvent
{
    /**
     * @var UserId
     */
    private UserId $userId;

    /**
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $occurredOn;

    /**
     * UserArrived constructor.
     *
     * @param UserId $userId
     */
    public function __construct(UserId $userId)
    {
        $this->userId = $userId;
        $this->occurredOn = Clock::instance()->now();
    }

    /**
     * @inheritdoc
     */
    public function aggregateId(): string
    {
        return $this->userId->toString();
    }

    /**
     * @inheritdoc
     */
    public function payload(): array
    {
        return [
            'userId' => $this->userId->toString()
        ];
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
    public function name(): string
    {
        return 'UserArrived';
    }
}
