<?php

declare(strict_types=1);

namespace Gaming\Identity\Application\Bot\Query\GetBotByUsername;

use Gaming\Common\Bus\Request;

/**
 * @implements Request<GetBotByUsernameResponse>
 */
final class GetBotByUsername implements Request
{
    public function __construct(
        public readonly string $username
    ) {
    }
}
