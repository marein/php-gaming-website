<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Board;

enum Stone: int
{
    case None = 0;
    case Red = 1;
    case Yellow = 2;
}
