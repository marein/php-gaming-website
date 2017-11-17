<?php

namespace Gambling\User\Application\User;

use Gambling\Common\Application\ApplicationLifeCycle;
use Gambling\User\Application\User\Command\SignUpCommand;
use Gambling\User\Domain\Model\User\Credentials;
use Gambling\User\Domain\Model\User\User;
use Gambling\User\Domain\Model\User\Users;

final class UserService
{
    /**
     * @var ApplicationLifeCycle
     */
    private $applicationLifeCycle;

    /**
     * @var Users
     */
    private $users;

    /**
     * UserService constructor.
     *
     * @param ApplicationLifeCycle $applicationLifeCycle
     * @param Users                $users
     */
    public function __construct(ApplicationLifeCycle $applicationLifeCycle, Users $users)
    {
        $this->applicationLifeCycle = $applicationLifeCycle;
        $this->users = $users;
    }

    /**
     * Sign up the user and return the assigned id.
     *
     * @param SignUpCommand $command
     *
     * @return string
     */
    public function signUp(SignUpCommand $command): string
    {
        return $this->applicationLifeCycle->run(
            function () use ($command) {
                $user = User::signUp(
                    new Credentials(
                        $command->username(),
                        $command->password()
                    )
                );

                $this->users->save($user);

                return $user->id()->toString();
            }
        );
    }
}
