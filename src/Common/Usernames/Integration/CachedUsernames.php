<?php

declare(strict_types=1);

namespace Gaming\Common\Usernames\Integration;

use Gaming\Common\Usernames\Usernames;

final class CachedUsernames implements Usernames
{
    /**
     * @var array<string, array{username: ?string, expiresAt: int}>
     */
    private array $cache = [];

    public function __construct(
        private readonly Usernames $usernames,
        private readonly int $ttlInSeconds
    ) {
    }

    public function byIds(array $userIds): array
    {
        $now = time();
        $expiresAt = $now + $this->ttlInSeconds;

        $usernames = [];
        $userIdsToFetch = [];
        foreach ($userIds as $userId) {
            if (($this->cache[$userId]['expiresAt'] ?? 0) < $now) {
                $userIdsToFetch[] = $userId;
            } else {
                $usernames[$userId] = $this->cache[$userId]['username'];
            }
        }

        foreach ($this->usernames->byIds($userIdsToFetch) as $userId => $username) {
            $usernames[$userId] = $username;
            $this->cache[$userId] = ['username' => $username, 'expiresAt' => $expiresAt];
        }

        return $usernames;
    }
}
