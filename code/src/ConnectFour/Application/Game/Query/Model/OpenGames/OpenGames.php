<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Query\Model\OpenGames;

final class OpenGames
{
    /**
     * @var OpenGame[]
     */
    private $games;

    /**
     * OpenGames constructor.
     *
     * @param OpenGame[] $games
     */
    public function __construct(array $games)
    {
        $this->games = $games;
    }

    /**
     * @return OpenGame[]
     */
    public function games(): array
    {
        return $this->games;
    }
}
