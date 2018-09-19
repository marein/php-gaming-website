<?php
declare(strict_types=1);

namespace Gambling\ConnectFour\Domain\Game;

use Gambling\ConnectFour\Domain\Game\Board\Stone;
use Gambling\ConnectFour\Domain\Game\Exception\PlayerHasInvalidStoneException;
use PHPUnit\Framework\TestCase;

class PlayerTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldBeCreatedWithItsValues(): void
    {
        $id = uniqid();
        $stone = Stone::red();

        $player = new Player($id, $stone);

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
        $stone = Stone::none();

        new Player($id, $stone);
    }
}
