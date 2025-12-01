<?php

declare(strict_types=1);

namespace Gaming\Common\Usernames;

interface Usernames
{
    /**
     * It's guaranteed that all provided account IDs will be present in the result.
     *
     * @param string[] $accountIds
     *
     * @return array<string, string>
     */
    public function byIds(array $accountIds): array;
}
