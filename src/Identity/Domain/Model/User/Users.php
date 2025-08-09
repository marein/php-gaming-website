<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\User;

use Gaming\Common\Domain\Exception\ConcurrencyException;
use Gaming\Identity\Domain\Model\User\Exception\EmailAlreadyExistsException;
use Gaming\Identity\Domain\Model\User\Exception\UsernameAlreadyExistsException;
use Gaming\Identity\Domain\Model\User\Exception\UserNotFoundException;

interface Users
{
    public function nextIdentity(): UserId;

    /**
     * Enforces uniqueness for email and username in its set of users.
     *
     * @throws ConcurrencyException
     * @throws EmailAlreadyExistsException
     * @throws UsernameAlreadyExistsException
     */
    public function save(User $user): void;

    /**
     * @throws UserNotFoundException
     */
    public function get(UserId $userId): User;

    public function getByEmail(string $email): ?User;

    /**
     * @param UserId[] $userIds
     *
     * @return User[]
     */
    public function getByIds(array $userIds): array;
}
