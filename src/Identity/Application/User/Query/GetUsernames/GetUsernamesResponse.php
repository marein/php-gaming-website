<?php

declare(strict_types=1);

namespace Gaming\Identity\Application\User\Query\GetUsernames;

final class GetUsernamesResponse
{
    /**
     * @param array<string, string> $usernames
     */
    public function __construct(
        public readonly array $usernames
    ) {
    }
}
