<?php

namespace Gambling\Identity\Domain\Model\User;

interface Users
{
    /**
     * Save the user.
     *
     * @param User $user
     *
     * @return void
     */
    public function save(User $user): void;

    /**
     * Get a user if exists.
     *
     * @param UserId $userId
     *
     * @return User|null
     */
    public function get(UserId $userId): ?User;
}
