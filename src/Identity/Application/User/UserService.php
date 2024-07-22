<?php

declare(strict_types=1);

namespace Gaming\Identity\Application\User;

use Gaming\Identity\Application\User\Command\ArriveCommand;
use Gaming\Identity\Application\User\Command\SignUpCommand;
use Gaming\Identity\Application\User\Query\User as UserResponse;
use Gaming\Identity\Application\User\Query\UserQuery;
use Gaming\Identity\Domain\Model\User\Exception\UserAlreadySignedUpException;
use Gaming\Identity\Domain\Model\User\Exception\UserNotFoundException;
use Gaming\Identity\Domain\Model\User\User;
use Gaming\Identity\Domain\Model\User\UserId;
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
     * @throws UserAlreadySignedUpException
     * @throws UserNotFoundException
     */
    public function signUp(SignUpCommand $command): void
    {
        $user = $this->users->get(UserId::fromString($command->userId));

        $user->signUp($command->email, $command->username);

        $this->users->save($user);
    }

    public function user(UserQuery $query): UserResponse
    {
        $user = $this->users->get(UserId::fromString($query->userId));

        return new UserResponse(
            $query->userId,
            $user->username(),
            $user->isSignedUp()
        );
    }
}
