<?php
declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\User;

use Gaming\Common\Domain\Exception\ConcurrencyException;
use Gaming\Identity\Domain\Model\User\Exception\UserNotFoundException;

interface Users
{
    /**
     * Save the user.
     *
     * @param User $user
     *
     * @return void
     * @throws ConcurrencyException
     */
    public function save(User $user): void;

    /**
     * Get a user if exists.
     *
     * @param UserId $userId
     *
     * @return User
     * @throws UserNotFoundException
     */
    public function get(UserId $userId): User;
}
