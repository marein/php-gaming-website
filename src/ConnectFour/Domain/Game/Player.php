<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game;

use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\Exception\PlayerHasInvalidStoneException;

final class Player
{
    /**
     * @var string
     */
    private string $playerId;

    /**
     * @var Stone
     */
    private Stone $stone;

    /**
     * Player constructor.
     *
     * @param string $playerId
     * @param Stone $stone
     *
     * @throws PlayerHasInvalidStoneException
     */
    public function __construct(string $playerId, Stone $stone)
    {
        $this->playerId = $playerId;
        $this->stone = $stone;

        $this->guardPlayerHasCorrectStone($stone);
    }

    /*************************************************************
     *                          Guards
     *************************************************************/

    /**
     * Guard that the [Stone] is Stone::red() or Stone::yellow().
     *
     * @param Stone $stone
     *
     * @throws PlayerHasInvalidStoneException
     */
    private function guardPlayerHasCorrectStone(Stone $stone): void
    {
        if ($stone === Stone::none()) {
            throw new PlayerHasInvalidStoneException('Stone should be Stone::red() or Stone::yellow().');
        }
    }

    /*************************************************************
     *                          Getter
     *************************************************************/

    /**
     * @return string
     */
    public function id(): string
    {
        return $this->playerId;
    }

    /**
     * @return Stone
     */
    public function stone(): Stone
    {
        return $this->stone;
    }
}
