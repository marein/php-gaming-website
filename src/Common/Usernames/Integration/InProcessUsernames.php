<?php

declare(strict_types=1);

namespace Gaming\Common\Usernames\Integration;

use Gaming\Common\Bus\Bus;
use Gaming\Common\Usernames\Usernames;
use Gaming\Identity\Application\User\Query\GetUsernames\GetUsernames;

final class InProcessUsernames implements Usernames
{
    public function __construct(
        private readonly Bus $identityBus
    ) {
    }

    public function byIds(array $userIds): array
    {
        if (count($userIds) === 0) {
            return [];
        }

        return $this->identityBus
            ->handle(new GetUsernames($userIds))
            ->usernames;
    }
}
