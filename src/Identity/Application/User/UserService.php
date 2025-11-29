<?php

declare(strict_types=1);

namespace Gaming\Identity\Application\User;

use Gaming\Common\Domain\Exception\ConcurrencyException;
use Gaming\Identity\Application\User\Command\ArriveCommand;
use Gaming\Identity\Application\User\Command\SignUpCommand;
use Gaming\Identity\Application\User\Query\User as UserResponse;
use Gaming\Identity\Application\User\Query\UserByEmailQuery;
use Gaming\Identity\Application\User\Query\UserQuery;
use Gaming\Identity\Domain\Model\Account\AccountId;
use Gaming\Identity\Domain\Model\User\Exception\EmailAlreadyExistsException;
use Gaming\Identity\Domain\Model\User\Exception\UserAlreadySignedUpException;
use Gaming\Identity\Domain\Model\User\Exception\UsernameAlreadyExistsException;
use Gaming\Identity\Domain\Model\User\Exception\UserNotFoundException;
use Gaming\Identity\Domain\Model\User\User;
use Gaming\Identity\Domain\Model\User\Users;

final class UserService
{
    public function __construct(
        private readonly Users $users
    ) {
    }

    public function arrive(ArriveCommand $command): string
    {
        $userId = $this->users->nextIdentity();

        $this->users->save(
            User::arrive($userId)
        );

        return $userId->toString();
    }

    /**
     * @throws ConcurrencyException
     * @throws EmailAlreadyExistsException
     * @throws UserAlreadySignedUpException
     * @throws UserNotFoundException
     * @throws UsernameAlreadyExistsException
     */
    public function signUp(SignUpCommand $command): void
    {
        $user = $this->users->get(AccountId::forUserId($command->userId));

        $user->signUp($command->email, $command->username);

        if ($command->dryRun) {
            return;
        }

        $this->users->save($user);
    }

    /**
     * @throws UserNotFoundException
     */
    public function user(UserQuery $query): UserResponse
    {
        $user = $this->users->get(AccountId::forUserId($query->userId));

        return new UserResponse(
            $query->userId,
            $user->username(),
            $user->isSignedUp()
        );
    }

    public function userByEmail(UserByEmailQuery $query): ?UserResponse
    {
        $user = $this->users->getByEmail($query->email);

        return $user === null ? null : new UserResponse(
            $user->id()->toString(),
            $user->username(),
            $user->isSignedUp()
        );
    }
}
