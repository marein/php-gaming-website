<?php

declare(strict_types=1);

namespace Gaming\Identity\Port\Adapter\Persistence\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Gaming\Common\Domain\Exception\ConcurrencyException;
use Gaming\Identity\Domain\Model\User\Exception\UserNotFoundException;
use Gaming\Identity\Domain\Model\User\User;
use Gaming\Identity\Domain\Model\User\UserId;
use Gaming\Identity\Domain\Model\User\Users;

final class DoctrineUserRepository implements Users
{
    public function __construct(
        private readonly EntityManager $manager
    ) {
    }

    public function nextIdentity(): UserId
    {
        return UserId::generate();
    }

    public function save(User $user): void
    {
        try {
            $this->manager->persist($user);
            $this->manager->flush();
        } catch (OptimisticLockException $exception) {
            throw new ConcurrencyException();
        }
    }

    public function get(UserId $userId): User
    {
        return $this->manager->getRepository(User::class)
            ->find($userId) ?? throw new UserNotFoundException();
    }
}
