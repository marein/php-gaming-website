<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game;

use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\Exception\PlayerNotOwnerException;
use Gaming\ConnectFour\Domain\Game\Exception\PlayersNotUniqueException;
use Gaming\ConnectFour\Domain\Game\Player;
use Gaming\ConnectFour\Domain\Game\Players;
use PHPUnit\Framework\TestCase;

class PlayersTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldThrowAnExceptionIfPlayersNotUnique(): void
    {
        $this->expectException(PlayersNotUniqueException::class);

        new Players(
            new Player('0', Stone::yellow()),
            new Player('0', Stone::yellow())
        );
    }

    /**
     * @test
     */
    public function itShouldSwitchPlayers(): void
    {
        $players = $this->players();

        $this->assertEquals($this->redPlayer(), $players->switch()->current());
    }

    /**
     * @test
     */
    public function itShouldReturnCurrentPlayer(): void
    {
        $players = $this->players();

        $this->assertEquals($this->yellowPlayer(), $players->current());
    }

    /**
     * @test
     */
    public function itShouldReturnPlayerById(): void
    {
        $players = $this->players();

        $this->assertEquals(
            $this->yellowPlayer(),
            $players->get($this->yellowPlayer()->id())
        );
        $this->assertEquals(
            $this->redPlayer(),
            $players->get($this->redPlayer()->id())
        );
    }

    /**
     * @test
     */
    public function itShouldThrowAnExceptionIfPlayerByIdGetsInvalidPlayer(): void
    {
        $this->expectException(PlayerNotOwnerException::class);

        $this->players()->get('eve');
    }

    /**
     * @test
     */
    public function itShouldReturnTheOpponentOfPlayer(): void
    {
        $players = $this->players();

        $this->assertEquals(
            $this->redPlayer(),
            $players->opponentOf($this->yellowPlayer()->id())
        );
        $this->assertEquals(
            $this->yellowPlayer(),
            $players->opponentOf($this->redPlayer()->id())
        );
    }

    /**
     * @test
     */
    public function itShouldThrowAnExceptionIfOpponentOfGetsInvalidPlayer(): void
    {
        $this->expectException(PlayerNotOwnerException::class);

        $this->players()->opponentOf('eve');
    }

    private function players(): Players
    {
        return new Players(
            $this->yellowPlayer(),
            $this->redPlayer()
        );
    }

    private function yellowPlayer(): Player
    {
        return new Player('0', Stone::yellow());
    }

    private function redPlayer(): Player
    {
        return new Player('1', Stone::Red);
    }
}
