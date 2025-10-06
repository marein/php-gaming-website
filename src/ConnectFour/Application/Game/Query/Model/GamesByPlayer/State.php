<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer;

enum State: string
{
    case All = 'all';
    case Running = 'running';
    case Won = 'won';
    case Lost = 'lost';
    case Drawn = 'drawn';
}
