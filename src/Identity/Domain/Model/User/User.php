<?php
declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\User;

use Gaming\Common\Domain\AggregateRoot;
use Gaming\Common\Domain\IsAggregateRoot;
use Gaming\Identity\Domain\HashAlgorithm;
use Gaming\Identity\Domain\Model\User\Event\UserArrived;
use Gaming\Identity\Domain\Model\User\Event\UserSignedUp;
use Gaming\Identity\Domain\Model\User\Exception\UserAlreadySignedUpException;

class User implements AggregateRoot
{
    use IsAggregateRoot;

    /**
     * @var UserId
     */
    private UserId $userId;

    /**
     * This version is for optimistic concurrency control.
     *
     * @var integer|null
     */
    private ?int $version;

    /**
     * @var bool
     */
    private bool $isSignedUp;

    /**
     * @var Credentials|null
     */
    private ?Credentials $credentials;

    /**
     * User constructor.
     *
     * @param UserId $userId
     */
    private function __construct(UserId $userId)
    {
        $this->userId = $userId;
        $this->version = null;
        $this->isSignedUp = false;
        $this->credentials = null;
    }

    /**
     * A new user arrives.
     *
     * @return User
     */
    public static function arrive(): User
    {
        $user = new self(
            UserId::generate()
        );

        $user->domainEvents[] = new UserArrived(
            $user->userId
        );

        return $user;
    }

    /**
     * The user signs up.
     *
     * @param Credentials $credentials
     *
     * @throws UserAlreadySignedUpException
     */
    public function signUp(Credentials $credentials): void
    {
        if ($this->isSignedUp) {
            throw new UserAlreadySignedUpException();
        }

        $this->isSignedUp = true;
        $this->credentials = $credentials;

        $this->domainEvents[] = new UserSignedUp(
            $this->userId,
            $this->credentials->username()
        );
    }

    /**
     * Returns true if the user can authenticate.
     *
     * todo: We can raise an UserAuthenticationAttempted event.
     *
     * @param string        $password
     * @param HashAlgorithm $hashAlgorithm
     *
     * @return bool
     */
    public function authenticate(string $password, HashAlgorithm $hashAlgorithm): bool
    {
        if (!$this->isSignedUp || $this->credentials === null) {
            return false;
        }

        return $this->credentials->matches($password, $hashAlgorithm);
    }

    /**
     * @return UserId
     */
    public function id(): UserId
    {
        return $this->userId;
    }
}
