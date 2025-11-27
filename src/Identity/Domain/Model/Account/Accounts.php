<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\Account;

interface Accounts
{
    /**
     * @param AccountId[] $accountIds
     *
     * @return Account[]
     */
    public function getByIds(array $accountIds): array;
}
