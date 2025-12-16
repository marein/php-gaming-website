<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\Bot\Exception;

use Gaming\Common\Domain\Exception\Violation;
use Gaming\Common\Domain\Exception\Violations;

final class UsernameAlreadyExistsException extends BotException
{
    public function __construct()
    {
        parent::__construct(new Violations(new Violation('username_already_exists')));
    }
}
