<?php

namespace Gambling\Identity\Domain\Model\User\Event;

use Gambling\Common\Clock\Clock;
use Gambling\Common\Domain\DomainEvent;
use Gambling\Identity\Domain\Model\User\UserId;

final class UserArrived implements DomainEvent
{
    /**
     * @var UserId
     */
    private $userId;

    /**
     * @var \DateTimeImmutable
     */
    private $occurredOn;

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
    public function occurredOn(): \DateTimeImmutable
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
