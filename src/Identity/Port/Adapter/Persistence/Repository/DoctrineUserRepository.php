<?php

declare(strict_types=1);

namespace Gaming\Identity\Port\Adapter\Persistence\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Gaming\Common\Domain\DomainEventPublisher;
use Gaming\Common\Domain\Exception\ConcurrencyException;
use Gaming\Identity\Domain\Model\User\Exception\UserNotFoundException;
use Gaming\Identity\Domain\Model\User\User;
use Gaming\Identity\Domain\Model\User\UserId;
use Gaming\Identity\Domain\Model\User\Users;

final class DoctrineUserRepository implements Users
{
    private EntityManager $manager;

    private DomainEventPublisher $domainEventPublisher;

    public function __construct(EntityManager $manager, DomainEventPublisher $domainEventPublisher)
    {
        $this->manager = $manager;
        $this->domainEventPublisher = $domainEventPublisher;
    }

    public function nextIdentity(): UserId
    {
        return UserId::generate();
    }

    public function save(User $user): void
    {
        $this->domainEventPublisher->publish($user->flushDomainEvents());

        try {
            $this->manager->persist($user);
            $this->manager->flush();
        } catch (OptimisticLockException $exception) {
            throw new ConcurrencyException();
        }
    }

    public function get(UserId $userId): User
    {
        $repository = $this->manager->getRepository(User::class);

        $user = $repository->find($userId);

        if ($user === null) {
            throw new UserNotFoundException();
        }

        assert($user instanceof User);

        return $user;
    }
}
