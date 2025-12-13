<?php

declare(strict_types=1);

namespace Gaming\Chat\Application\Exception;

use Gaming\Common\Domain\Exception\Violation;
use Gaming\Common\Domain\Exception\Violations;

final class ChatNotFoundException extends ChatException
{
    public function __construct()
    {
        parent::__construct(new Violations(new Violation('chat_not_found')));
    }
}
