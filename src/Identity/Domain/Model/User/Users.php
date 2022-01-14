<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\User;

use Gaming\Common\Domain\Exception\ConcurrencyException;
use Gaming\Identity\Domain\Model\User\Exception\UserNotFoundException;

interface Users
{
    /**
     * @throws ConcurrencyException
     */
    public function save(User $user): void;

    /**
     * @throws UserNotFoundException
     */
    public function get(UserId $userId): User;
}
