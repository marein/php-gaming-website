<?php

declare(strict_types=1);

namespace Gaming\Identity\Application\User\Query;

use Gaming\Common\Bus\Request;

/**
 * @implements Request<User>
 */
final class UserQuery implements Request
{
    public function __construct(
        public readonly string $userId
    ) {
    }
}
