<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer;

enum State: string
{
    case All = 'all';
    case Open = 'open';
    case Running = 'running';
    case Won = 'won';
    case Lost = 'lost';
    case Drawn = 'drawn';

    /**
     * @return State[]
     */
    public static function visibleCases(): array
    {
        return [self::All, self::Running, self::Won, self::Lost, self::Drawn];
    }
}
