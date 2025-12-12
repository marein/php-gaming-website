<?php

declare(strict_types=1);

namespace Gaming\Chat\Application\Exception;

use Gaming\Common\Domain\Exception\Violation;
use Gaming\Common\Domain\Exception\Violations;

final class ChatAlreadyExistsException extends ChatException
{
    public function __construct()
    {
        parent::__construct(new Violations(new Violation('chat_already_exists')));
    }
}
