<?php

namespace Gambling\Identity\Port\Adapter\Persistence\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Gambling\Common\Domain\DomainEventPublisher;
use Gambling\Common\Domain\Exception\ConcurrencyException;
use Gambling\Identity\Domain\Model\User\Exception\UserNotFoundException;
use Gambling\Identity\Domain\Model\User\User;
use Gambling\Identity\Domain\Model\User\UserId;
use Gambling\Identity\Domain\Model\User\Users;

final class DoctrineUserRepository implements Users
{
    /**
     * @var EntityManager
     */
    private $manager;

    /**
     * @var DomainEventPublisher
     */
    private $domainEventPublisher;

    /**
     * DoctrineUserRepository constructor.
     *
     * @param EntityManager        $manager
     * @param DomainEventPublisher $domainEventPublisher
     */
    public function __construct(EntityManager $manager, DomainEventPublisher $domainEventPublisher)
    {
        $this->manager = $manager;
        $this->domainEventPublisher = $domainEventPublisher;
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function get(UserId $userId): User
    {
        $repository = $this->manager->getRepository(User::class);

        /** @var User|null $user */
        $user = $repository->find($userId);

        if ($user === null) {
            throw new UserNotFoundException();
        }

        return $user;
    }
}
