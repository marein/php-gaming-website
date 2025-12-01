<?php

declare(strict_types=1);

namespace Gaming\Identity\Port\Adapter\Persistence\Repository;

use Doctrine\ORM\EntityManager;
use Gaming\Identity\Domain\Model\Account\Account;
use Gaming\Identity\Domain\Model\Account\AccountId;
use Gaming\Identity\Domain\Model\Account\Accounts;
use Symfony\Component\Uid\Uuid;

final class DoctrineAccountRepository implements Accounts
{
    public function __construct(
        private readonly EntityManager $manager
    ) {
    }

    public function getByIds(array $accountIds): array
    {
        return $this->manager->getRepository(Account::class)->findBy(
            [
                'accountId.accountId' => array_map(
                    static fn(AccountId $accountId): string => Uuid::fromRfc4122($accountId->toString())->toBinary(),
                    $accountIds
                )
            ]
        );
    }
}
