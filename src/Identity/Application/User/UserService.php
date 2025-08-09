<?php

declare(strict_types=1);

namespace Gaming\Identity\Application\User;

use Gaming\Identity\Application\User\Command\ArriveCommand;
use Gaming\Identity\Application\User\Command\SignUpCommand;
use Gaming\Identity\Application\User\Query\GetUsernames\GetUsernames;
use Gaming\Identity\Application\User\Query\GetUsernames\GetUsernamesResponse;
use Gaming\Identity\Application\User\Query\User as UserResponse;
use Gaming\Identity\Application\User\Query\UserByEmailQuery;
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

        if ($command->dryRun) {
            return;
        }

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

    public function userByEmail(UserByEmailQuery $query): ?UserResponse
    {
        $user = $this->users->getByEmail($query->email);

        return $user === null ? null : new UserResponse(
            $user->id()->toString(),
            $user->username(),
            $user->isSignedUp()
        );
    }

    public function getUsernames(GetUsernames $query): GetUsernamesResponse
    {
        $users = $this->users->getByIds(
            array_map(static fn(string $userId): UserId => UserId::fromString($userId), $query->userIds)
        );

        $usernames = array_combine($query->userIds, array_fill(0, count($query->userIds), null));
        foreach ($users as $user) {
            $usernames[$user->id()->toString()] = $user->username() !== '' ? $user->username() : null;
        }

        return new GetUsernamesResponse($usernames);
    }
}
