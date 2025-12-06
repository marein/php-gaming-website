<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Exception;

use Gaming\Common\Domain\Exception\Violation;
use Gaming\Common\Domain\Exception\Violations;

final class GameFinishedException extends GameException
{
    public function __construct()
    {
        parent::__construct(new Violations(new Violation('game_already_finished')));
    }
}
