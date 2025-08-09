<?php

declare(strict_types=1);

namespace Gaming\Common\Usernames;

interface Usernames
{
    /**
     * @param string[] $userIds
     *
     * @return array<string, string>
     */
    public function byIds(array $userIds): array;
}
