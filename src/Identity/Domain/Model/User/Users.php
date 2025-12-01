<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\User;

use Gaming\Common\Domain\Exception\ConcurrencyException;
use Gaming\Identity\Domain\Model\Account\AccountId;
use Gaming\Identity\Domain\Model\User\Exception\EmailAlreadyExistsException;
use Gaming\Identity\Domain\Model\User\Exception\UsernameAlreadyExistsException;
use Gaming\Identity\Domain\Model\User\Exception\UserNotFoundException;

interface Users
{
    public function nextIdentity(): AccountId;

    /**
     * Enforces uniqueness for email and username in the set of accounts.
     *
     * @throws ConcurrencyException
     * @throws EmailAlreadyExistsException
     * @throws UsernameAlreadyExistsException
     */
    public function save(User $user): void;

    /**
     * @throws UserNotFoundException
     */
    public function get(AccountId $userId): User;

    public function getByEmail(string $email): ?User;
}
