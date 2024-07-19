<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\User;

use Gaming\Common\EventStore\CollectsDomainEvents;
use Gaming\Common\EventStore\DomainEvent;
use Gaming\Identity\Domain\Model\User\Event\UserArrived;
use Gaming\Identity\Domain\Model\User\Event\UserSignedUp;
use Gaming\Identity\Domain\Model\User\Exception\UserAlreadySignedUpException;

class User implements CollectsDomainEvents
{
    private UserId $userId;

    /**
     * @var DomainEvent[]
     */
    private array $domainEvents = [];

    /**
     * This version is for optimistic concurrency control.
     */
    private ?int $version;

    private ?string $email;

    private ?string $username;

    private function __construct(UserId $userId)
    {
        $this->userId = $userId;
        $this->version = null;
        $this->email = null;
        $this->username = null;
    }

    public static function arrive(UserId $userId): User
    {
        $user = new self($userId);

        $user->domainEvents[] = new DomainEvent(
            $user->userId->toString(),
            new UserArrived($user->userId)
        );

        return $user;
    }

    /**
     * @throws UserAlreadySignedUpException
     */
    public function signUp(string $email, string $username): void
    {
        if ($this->email !== null) {
            throw new UserAlreadySignedUpException();
        }

        $this->email = $email;
        $this->username = $username;

        $this->domainEvents[] = new DomainEvent(
            $this->userId->toString(),
            new UserSignedUp($this->userId, $this->email, $this->username)
        );
    }

    public function flushDomainEvents(): array
    {
        return array_splice($this->domainEvents, 0);
    }
}
