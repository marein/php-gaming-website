<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game;

use Gaming\Common\Domain\Test\DomainAssert;
use Gaming\Common\Timer\GameTimer;
use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\Exception\PlayerHasInvalidStoneException;
use Gaming\ConnectFour\Domain\Game\Player;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PlayerTest extends TestCase
{
    #[Test]
    public function itShouldBeCreatedWithItsValues(): void
    {
        $id = uniqid();
        $stone = Stone::Red;

        $player = new Player($id, $stone, GameTimer::set(60000, 0));

        $this->assertEquals($id, $player->id());
        $this->assertEquals($stone, $player->stone());
    }

    #[Test]
    public function itShouldNotBeCreatedWithNoneStone(): void
    {
        DomainAssert::expectViolation(
            fn() => new Player(uniqid(), Stone::None, GameTimer::set(60000, 0)),
            PlayerHasInvalidStoneException::class,
            'player_has_invalid_stone'
        );
    }
}
