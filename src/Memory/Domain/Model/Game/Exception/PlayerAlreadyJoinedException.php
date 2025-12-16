<?php

declare(strict_types=1);

namespace Gaming\Memory\Domain\Model\Game\Exception;

use Gaming\Common\Domain\Exception\Violation;
use Gaming\Common\Domain\Exception\Violations;

final class PlayerAlreadyJoinedException extends GameException
{
    public function __construct()
    {
        parent::__construct(new Violations(new Violation('player_already_joined')));
    }
}
