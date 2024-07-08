<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\User;

use Gaming\Common\Domain\Exception\ConcurrencyException;
use Gaming\Identity\Domain\Model\User\Exception\DuplicateEmailException;
use Gaming\Identity\Domain\Model\User\Exception\DuplicateUsernameException;
use Gaming\Identity\Domain\Model\User\Exception\UserNotFoundException;

interface Users
{
    public function nextIdentity(): UserId;

    /**
     * Enforces uniqueness for email and username in its set of users.
     *
     * @throws DuplicateEmailException
     * @throws DuplicateUsernameException
     * @throws ConcurrencyException
     */
    public function save(User $user): void;

    /**
     * @throws UserNotFoundException
     */
    public function get(UserId $userId): User;
}
