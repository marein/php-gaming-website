<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer;

enum State: string
{
    case ALL = 'all';
    case RUNNING = 'running';
    case WON = 'won';
    case LOST = 'lost';
    case DRAWN = 'drawn';
}
