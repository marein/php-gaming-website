<?php

namespace Gambling\Identity\Domain\Model\User;

use Gambling\Common\Domain\AggregateRoot;
use Gambling\Common\Domain\IsAggregateRoot;
use Gambling\Identity\Domain\Model\User\Event\UserSignedUp;

class User implements AggregateRoot
{
    use IsAggregateRoot;

    /**
     * @var UserId
     */
    private $userId;

    /**
     * @var Credentials
     */
    private $credentials;

    /**
     * User constructor.
     *
     * @param UserId      $userId
     * @param Credentials $credentials
     */
    private function __construct(UserId $userId, Credentials $credentials)
    {
        $this->userId = $userId;
        $this->credentials = $credentials;
    }

    /**
     * Sign up the user.
     *
     * @param Credentials $credentials
     *
     * @return User
     */
    public static function signUp(Credentials $credentials): User
    {
        $user = new self(
            UserId::generate(),
            $credentials
        );

        $user->domainEvents[] = new UserSignedUp(
            $user->id(),
            $user->credentials->username()
        );

        return $user;
    }

    /**
     * @return UserId
     */
    public function id(): UserId
    {
        return $this->userId;
    }

    /**
     * @return Credentials
     */
    public function credentials(): Credentials
    {
        return $this->credentials;
    }
}
