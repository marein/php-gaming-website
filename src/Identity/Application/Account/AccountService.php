<?php

declare(strict_types=1);

namespace Gaming\Identity\Application\Account;

use Gaming\Identity\Application\Account\Query\GetUsernames\GetUsernames;
use Gaming\Identity\Application\Account\Query\GetUsernames\GetUsernamesResponse;
use Gaming\Identity\Domain\Model\Account\AccountId;
use Gaming\Identity\Domain\Model\Account\Accounts;
use Gaming\Identity\Domain\Model\Account\UsernameGenerator;

final class AccountService
{
    public function __construct(
        private readonly Accounts $accounts
    ) {
    }

    public function getUsernames(GetUsernames $query): GetUsernamesResponse
    {
        $accounts = $this->accounts->getByIds(
            array_map(static fn(string $accountId): AccountId => AccountId::fromString($accountId), $query->accountIds)
        );

        $usernames = array_combine(
            $query->accountIds,
            array_fill(0, count($query->accountIds), UsernameGenerator::dummy())
        );
        foreach ($accounts as $account) {
            $usernames[$account->id()->toString()] = $account->username();
        }

        return new GetUsernamesResponse($usernames);
    }
}
