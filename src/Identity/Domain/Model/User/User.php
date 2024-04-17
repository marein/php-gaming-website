<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\User;

use Gaming\Common\EventStore\CollectsDomainEvents;
use Gaming\Common\EventStore\DomainEvent;
use Gaming\Identity\Domain\HashAlgorithm;
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

    private bool $isSignedUp;

    private ?Credentials $credentials;

    private function __construct(UserId $userId)
    {
        $this->userId = $userId;
        $this->version = null;
        $this->isSignedUp = false;
        $this->credentials = null;
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
    public function signUp(Credentials $credentials): void
    {
        if ($this->isSignedUp) {
            throw new UserAlreadySignedUpException();
        }

        $this->isSignedUp = true;
        $this->credentials = $credentials;

        $this->domainEvents[] = new DomainEvent(
            $this->userId->toString(),
            new UserSignedUp($this->userId, $this->credentials->username())
        );
    }

    /**
     * todo: We can raise an UserAuthenticationAttempted event.
     */
    public function authenticate(string $password, HashAlgorithm $hashAlgorithm): bool
    {
        if (!$this->isSignedUp || $this->credentials === null) {
            return false;
        }

        return $this->credentials->matches($password, $hashAlgorithm);
    }

    public function flushDomainEvents(): array
    {
        return array_splice($this->domainEvents, 0);
    }
}
