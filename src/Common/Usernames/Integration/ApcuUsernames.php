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

    public function byIds(array $userIds): array
    {
        $usernames = apcu_fetch($userIds);
        $missingUserIds = array_diff($userIds, array_keys($usernames));

        if (count($missingUserIds) !== 0) {
            $fetchedUsernames = $this->usernames->byIds($missingUserIds);
            apcu_add($fetchedUsernames, null, $this->ttlInSeconds);
            $usernames = array_merge($usernames, $fetchedUsernames);
        }

        return $usernames;
    }
}
