<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\User\Exception;

use Gaming\Common\Domain\Exception\Violation;
use Gaming\Common\Domain\Exception\Violations;

class UserAlreadySignedUpException extends UserException
{
    public function __construct()
    {
        parent::__construct(new Violations(new Violation('user_already_signed_up')));
    }
}
