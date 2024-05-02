<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query;

use Gaming\Common\Bus\Request;
use Gaming\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGames;

/**
 * @implements Request<OpenGames>
 */
final class OpenGamesQuery implements Request
{
}
