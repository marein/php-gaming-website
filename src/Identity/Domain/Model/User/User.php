<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\User;

use Gaming\Identity\Domain\Model\Account\Account;
use Gaming\Identity\Domain\Model\Account\AccountId;
use Gaming\Identity\Domain\Model\User\Event\UserArrived;
use Gaming\Identity\Domain\Model\User\Event\UserSignedUp;
use Gaming\Identity\Domain\Model\User\Exception\UserAlreadySignedUpException;

class User extends Account
{
    private ?string $email = null;

    public static function arrive(AccountId $userId): self
    {
        $user = new self($userId);

        $user->record(new UserArrived($user->accountId));

        return $user;
    }

    /**
     * @throws UserAlreadySignedUpException
     */
    public function signUp(string $email, string $username): void
    {
        if ($this->isSignedUp()) {
            throw new UserAlreadySignedUpException();
        }

        $this->email = $email;
        $this->username = $username;

        $this->record(new UserSignedUp($this->accountId, $this->email, $this->username));
    }

    public function isSignedUp(): bool
    {
        return $this->email !== null && $this->username !== null;
    }
}
