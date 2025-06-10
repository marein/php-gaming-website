<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game;

use Gaming\Common\Timer\TimePerGame;
use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\Exception\PlayerHasInvalidStoneException;
use Gaming\ConnectFour\Domain\Game\Player;
use PHPUnit\Framework\TestCase;

class PlayerTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldBeCreatedWithItsValues(): void
    {
        $id = uniqid();
        $stone = Stone::Red;

        $player = new Player($id, $stone, TimePerGame::set(300, 5));

        $this->assertEquals($id, $player->id());
        $this->assertEquals($stone, $player->stone());
    }

    /**
     * @test
     */
    public function itShouldNotBeCreatedWithNoneStone(): void
    {
        $this->expectException(PlayerHasInvalidStoneException::class);

        $id = uniqid();
        $stone = Stone::None;

        new Player($id, $stone, TimePerGame::set(300, 5));
    }
}
