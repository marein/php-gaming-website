<?php

declare(strict_types=1);

namespace Gaming\Identity\Application\Bot\Command\RegisterBot;

use Gaming\Common\Bus\Request;

/**
 * @implements Request<RegisterBotResponse>
 */
final class RegisterBot implements Request
{
    public function __construct(
        public readonly string $username
    ) {
    }
}
