<?php

declare(strict_types=1);

namespace Gaming\Common\Usernames\Integration;

use Gaming\Common\Usernames\Usernames;

final class ApcuUsernames implements Usernames
{
    public function __construct(
        private readonly Usernames $usernames,
        private readonly int $ttlInSeconds
    ) {
    }

    public function byIds(array $accountIds): array
    {
        $usernames = apcu_fetch($accountIds);
        $missingAccountIds = array_diff($accountIds, array_keys($usernames));

        if (count($missingAccountIds) !== 0) {
            $fetchedUsernames = $this->usernames->byIds($missingAccountIds);
            apcu_add($fetchedUsernames, null, $this->ttlInSeconds);
            $usernames = array_merge($usernames, $fetchedUsernames);
        }

        return $usernames;
    }
}
