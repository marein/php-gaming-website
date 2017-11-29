<?php

namespace Gambling\User\Domain\Model\User\Event;

use Gambling\Common\Clock\Clock;
use Gambling\Common\Domain\DomainEvent;
use Gambling\User\Domain\Model\User\UserId;

final class UserSignedUp implements DomainEvent
{
    /**
     * @var UserId
     */
    private $userId;

    /**
     * @var string
     */
    private $username;

    /**
     * @var \DateTimeImmutable
     */
    private $occurredOn;


    public function __construct(UserId $userId, string $username)
    {
        $this->userId = $userId;
        $this->username = $username;
        $this->occurredOn = Clock::instance()->now();
    }

    /**
     * @inheritdoc
     */
    public function payload(): array
    {
        return [
            'userId'   => $this->userId->toString(),
            'username' => $this->username
        ];
    }

    /**
     * @inheritdoc
     */
    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }

    public function name(): string
    {
        return 'user.user-signed-up';
    }
}
