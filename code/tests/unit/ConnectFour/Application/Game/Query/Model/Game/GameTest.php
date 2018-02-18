<?php

namespace Gambling\ConnectFour\Application\Game\Query\Model\Game;

use Gambling\Common\EventStore\StoredEvent;
use PHPUnit\Framework\TestCase;

class GameTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldProjectEvents(): void
    {
        $expectedGameId = 'gameId';
        $expectedFinished = false;
        $expectedSerializedGame = json_encode([
            'gameId'   => $expectedGameId,
            'chatId'   => 'chatId',
            'finished' => $expectedFinished,
            'height'   => 6,
            'width'    => 7,
            'moves'    => [
                [
                    'x'     => 1,
                    'y'     => 1,
                    'color' => 1
                ],
                [
                    'x'     => 1,
                    'y'     => 2,
                    'color' => 2
                ]
            ]
        ]);

        $game = new Game();
        foreach ($this->storedEvents() as $storedEvent) {
            $game->apply($storedEvent);
        }

        $this->assertEquals($expectedGameId, $game->id());
        $this->assertEquals($expectedFinished, $game->finished());
        // Implicitly test if it's serializable.
        $this->assertEquals($expectedSerializedGame, json_encode($game));
    }

    /**
     * @test
     */
    public function itShouldBeMarkedAsFinishedWhenGameAborted(): void
    {
        $game = new Game();
        $game->apply(
            new StoredEvent(
                1,
                'GameAborted',
                'gameId',
                '{}',
                new \DateTimeImmutable()
            )
        );

        $this->assertEquals(true, $game->finished());
        $this->assertEquals(true, $game->jsonSerialize()['finished']);
    }

    /**
     * @test
     */
    public function itShouldBeMarkedAsFinishedWhenGameResigned(): void
    {
        $game = new Game();
        $game->apply(
            new StoredEvent(
                1,
                'GameResigned',
                'gameId',
                '{}',
                new \DateTimeImmutable()
            )
        );

        $this->assertEquals(true, $game->finished());
        $this->assertEquals(true, $game->jsonSerialize()['finished']);
    }

    /**
     * @test
     */
    public function itShouldBeMarkedAsFinishedWhenGameWon(): void
    {
        $game = new Game();
        $game->apply(
            new StoredEvent(
                1,
                'GameWon',
                'gameId',
                '{}',
                new \DateTimeImmutable()
            )
        );

        $this->assertEquals(true, $game->finished());
        $this->assertEquals(true, $game->jsonSerialize()['finished']);
    }

    /**
     * @test
     */
    public function itShouldBeMarkedAsFinishedWhenGameDrawn(): void
    {
        $game = new Game();
        $game->apply(
            new StoredEvent(
                1,
                'GameDrawn',
                'gameId',
                '{}',
                new \DateTimeImmutable()
            )
        );

        $this->assertEquals(true, $game->finished());
        $this->assertEquals(true, $game->jsonSerialize()['finished']);
    }

    /**
     * Stored events which gets applied.
     *
     * @return StoredEvent[]
     */
    private function storedEvents(): array
    {
        return [
            new StoredEvent(
                1,
                'GameOpened',
                'gameId',
                '{"gameId": "gameId", "width": 7, "height": 6, "playerId": "player1"}',
                new \DateTimeImmutable()
            ),
            new StoredEvent(
                2,
                'ChatAssigned',
                'gameId',
                '{"gameId": "gameId", "chatId": "chatId"}',
                new \DateTimeImmutable()
            ),
            new StoredEvent(
                3,
                'PlayerJoined',
                'gameId',
                '{"gameId": "gameId", "opponentPlayerId": "player1", "joinedPlayerId": "player2"}',
                new \DateTimeImmutable()
            ),
            new StoredEvent(
                4,
                'PlayerMoved',
                'gameId',
                '{"gameId": "gameId", "x": 1, "y": 1, "color": 1}',
                new \DateTimeImmutable()
            ),
            new StoredEvent(
                5,
                'PlayerMoved',
                'gameId',
                '{"gameId": "gameId", "x": 1, "y": 2, "color": 2}',
                new \DateTimeImmutable()
            ),
            // Apply this event twice, so immutability gets tested.
            new StoredEvent(
                5,
                'PlayerMoved',
                'gameId',
                '{"gameId": "gameId", "x": 1, "y": 2, "color": 2}',
                new \DateTimeImmutable()
            )
        ];
    }
}
