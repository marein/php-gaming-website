<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Exception;

use Gaming\Common\Domain\Exception\Violation;
use Gaming\Common\Domain\Exception\Violations;

final class ColumnAlreadyFilledException extends GameException
{
    public function __construct()
    {
        parent::__construct(new Violations(new Violation('column_already_filled')));
    }
}
