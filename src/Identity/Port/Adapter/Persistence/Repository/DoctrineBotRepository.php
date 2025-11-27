<?php

declare(strict_types=1);

namespace Gaming\Identity\Port\Adapter\Persistence\Repository;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Gaming\Common\Domain\Exception\ConcurrencyException;
use Gaming\Identity\Domain\Model\Account\AccountId;
use Gaming\Identity\Domain\Model\Bot\Bot;
use Gaming\Identity\Domain\Model\Bot\Bots;
use Gaming\Identity\Domain\Model\Bot\Exception\UsernameAlreadyExistsException;

final class DoctrineBotRepository implements Bots
{
    public function __construct(
        private readonly EntityManager $manager
    ) {
    }

    public function nextIdentity(): AccountId
    {
        return AccountId::generate();
    }

    public function save(Bot $bot): void
    {
        try {
            $this->manager->persist($bot);
            $this->manager->flush();
        } catch (OptimisticLockException) {
            throw new ConcurrencyException();
        } catch (UniqueConstraintViolationException $e) {
            match (true) {
                str_contains($e->getMessage(), 'uniq_username') => throw new UsernameAlreadyExistsException(),
                default => throw $e
            };
        }
    }
}
