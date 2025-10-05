<?php

declare(strict_types=1);

namespace Gaming\Identity\Application\User\Query\GetUsernames;

use Gaming\Common\Bus\Request;

/**
 * @implements Request<GetUsernamesResponse>
 */
final class GetUsernames implements Request
{
    /**
     * @param array<string, string> $userIds
     */
    public function __construct(
        public readonly array $userIds
    ) {
    }
}
