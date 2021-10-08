<?php

declare(strict_types=1);

namespace Gaming\Identity\Application\User;

use Gaming\Identity\Application\User\Command\ArriveCommand;
use Gaming\Identity\Application\User\Command\SignUpCommand;
use Gaming\Identity\Domain\HashAlgorithm;
use Gaming\Identity\Domain\Model\User\Credentials;
use Gaming\Identity\Domain\Model\User\Exception\UserAlreadySignedUpException;
use Gaming\Identity\Domain\Model\User\Exception\UserNotFoundException;
use Gaming\Identity\Domain\Model\User\User;
use Gaming\Identity\Domain\Model\User\UserId;
use Gaming\Identity\Domain\Model\User\Users;

final class UserService
{
    /**
     * @var Users
     */
    private Users $users;

    /**
     * @var HashAlgorithm
     */
    private HashAlgorithm $hashAlgorithm;

    /**
     * UserService constructor.
     *
     * @param Users $users
     * @param HashAlgorithm $hashAlgorithm
     */
    public function __construct(Users $users, HashAlgorithm $hashAlgorithm)
    {
        $this->users = $users;
        $this->hashAlgorithm = $hashAlgorithm;
    }

    /**
     * A new user arrives.
     *
     * @param ArriveCommand $command
     *
     * @return string
     */
    public function arrive(ArriveCommand $command): string
    {
        $user = User::arrive();

        $this->users->save($user);

        return $user->id()->toString();
    }

    /**
     * Sign up the user.
     *
     * @param SignUpCommand $command
     *
     * @throws UserAlreadySignedUpException
     * @throws UserNotFoundException
     */
    public function signUp(SignUpCommand $command): void
    {
        $user = $this->users->get(
            UserId::fromString($command->userId())
        );

        $user->signUp(
            new Credentials(
                $command->username(),
                $command->password(),
                $this->hashAlgorithm
            )
        );

        $this->users->save($user);
    }
}
