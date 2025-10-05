<?php

declare(strict_types=1);

namespace Gaming\Common\Usernames;

interface Usernames
{
    /**
     * It's guaranteed that all provided user IDs will be present in the result.
     *
     * @param string[] $userIds
     *
     * @return array<string, string>
     */
    public function byIds(array $userIds): array;
}
