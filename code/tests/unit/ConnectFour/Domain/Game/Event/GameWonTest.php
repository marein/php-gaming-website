<?php
declare(strict_types=1);

namespace Gambling\ConnectFour\Domain\Game\Event;

use Gambling\Common\Clock\Clock;
use Gambling\ConnectFour\Domain\Game\Board\Stone;
use Gambling\ConnectFour\Domain\Game\GameId;
use Gambling\ConnectFour\Domain\Game\Player;
use PHPUnit\Framework\TestCase;

final class GameWonTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldBeCreatedWithItsValues(): void
    {
        Clock::instance()->freeze();

        $gameId = GameId::generate();
        $winnerPlayerId = 'winnerPlayerId';
        $payload = [
            'gameId'         => $gameId->toString(),
            'winnerPlayerId' => $winnerPlayerId
        ];

        $gameWon = new GameWon(
            $gameId,
            new Player($winnerPlayerId, Stone::red())
        );

        $this->assertSame('GameWon', $gameWon->name());
        $this->assertSame($gameId->toString(), $gameWon->aggregateId());
        $this->assertSame(Clock::instance()->now(), $gameWon->occurredOn());
        $this->assertSame($payload, $gameWon->payload());

        Clock::instance()->resume();
    }
}
