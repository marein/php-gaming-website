<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Security;

use DateInterval;
use Gaming\Common\Bus\Bus;
use Gaming\Identity\Application\User\Query\UserQuery;
use Gaming\Identity\Domain\Model\User\Exception\UserNotFoundException;
use Psr\Clock\ClockInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException as SymfonyUserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<User>
 */
final class UserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly Bus $identityQueryBus,
        private readonly ClockInterface $clock,
        private readonly int $refreshRateInSeconds = 60
    ) {
    }

    /**
     * As this UserProvider is based on an external API, the user is only refreshed at the configured interval.
     * This can cause the data in the session to be stale, e.g. the username.
     * If critical data is changed by the user themselves, they must be logged in again programmatically.
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        assert($user instanceof User);

        return $user->refreshAt >= $this->clock->now()
            ? $user
            : $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return $class === User::class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        try {
            $user = $this->identityQueryBus->handle(
                new UserQuery($identifier)
            );

            return new User(
                $user->userId,
                $user->username,
                $user->isSignedUp,
                $this->clock->now()->add(new DateInterval('PT' . abs($this->refreshRateInSeconds) . 'S'))
            );
        } catch (UserNotFoundException) {
            $exception = new SymfonyUserNotFoundException(
                sprintf('There is no user with identifier "%s".', $identifier)
            );
            $exception->setUserIdentifier($identifier);

            throw $exception;
        }
    }
}
