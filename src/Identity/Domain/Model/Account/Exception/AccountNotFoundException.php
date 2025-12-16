<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\Account\Exception;

use Gaming\Common\Domain\Exception\Violation;
use Gaming\Common\Domain\Exception\Violations;

final class AccountNotFoundException extends AccountException
{
    public function __construct()
    {
        parent::__construct(new Violations(new Violation('account_not_found')));
    }
}
