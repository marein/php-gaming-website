<?php
declare(strict_types=1);

namespace Gambling\Memory\Domain\Model\Game;

use Gambling\Memory\Domain\Model\Game\Dealer\LazyDealer;
use Gambling\Memory\Domain\Model\Game\Event\GameOpened;
use PHPUnit\Framework\TestCase;

class GameTest extends TestCase
{
    /**
     * @test
     */
    public function aGameCanBeOpened(): void
    {
        $game = Game::open(
            new LazyDealer(5),
            'playerId1'
        );

        $domainEvents = $game->flushDomainEvents();
        $gameOpened = $domainEvents[0];

        $this->assertCount(1, $domainEvents);
        $this->assertInstanceOf(GameOpened::class, $gameOpened);
        $this->assertSame($game->id()->toString(), $gameOpened->aggregateId());
        $this->assertSame(10, $gameOpened->payload()['numberOfCards']);
        $this->assertSame('playerId1', $gameOpened->payload()['playerId']);
    }
}
