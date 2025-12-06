<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Exception;

use Gaming\Common\Domain\Exception\Violation;
use Gaming\Common\Domain\Exception\Violations;

final class PlayerHasInvalidStoneException extends GameException
{
    public function __construct()
    {
        parent::__construct(new Violations(new Violation('player_has_invalid_stone')));
    }
}
