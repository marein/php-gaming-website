<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Application\Game\Query\Model\Game;

use Gaming\ConnectFour\Application\Game\Query\Model\Game\Game;
use Gaming\ConnectFour\Domain\Game\Configuration;
use Gaming\ConnectFour\Domain\Game\Event\GameDrawn;
use Gaming\ConnectFour\Domain\Game\Game as DomainGame;
use Gaming\ConnectFour\Domain\Game\GameId;
use PHPUnit\Framework\TestCase;

class GameTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldProjectEvents(): void
    {
        $domainGame = DomainGame::open(GameId::generate(), Configuration::common(), 'player1');

        $expectedGameId = $domainGame->id()->toString();
        $expectedSerializedGame = json_encode(
            [
                'gameId' => $expectedGameId,
                'chatId' => 'chatId',
                'openedBy' => 'player1',
                'redPlayerId' => 'player1',
                'yellowPlayerId' => 'player2',
                'currentPlayerId' => 'player1',
                'winnerId' => '',
                'loserId' => '',
                'resignedBy' => '',
                'abortedBy' => '',
                'state' => 'running',
                'height' => 6,
                'width' => 7,
                'preferredStone' => 1,
                'moves' => [
                    [
                        'x' => 1,
                        'y' => 6,
                        'color' => 1
                    ],
                    [
                        'x' => 1,
                        'y' => 5,
                        'color' => 2
                    ]
                ],
                'winningSequences' => []
            ],
            JSON_THROW_ON_ERROR
        );

        $domainGame->assignChat('chatId');
        $domainGame->join('player2');
        $domainGame->move('player1', 1);
        $domainGame->move('player2', 1);

        $game = new Game();

        $this->assertEquals($game::STATE_OPEN, $game->state);

        $this->applyFromDomainGame($game, $domainGame);

        $this->assertEquals($expectedGameId, $game->id());
        $this->assertEquals($game::STATE_RUNNING, $game->state);
        // Implicitly test if it's serializable.
        $this->assertEquals($expectedSerializedGame, json_encode($game, JSON_THROW_ON_ERROR));
    }

    /**
     * @test
     */
    public function itShouldBeMarkedAsFinishedWhenGameAborted(): void
    {
        $domainGame = DomainGame::open(GameId::generate(), Configuration::common(), 'player1');
        $domainGame->abort('player1');

        $game = new Game();
        $this->applyFromDomainGame($game, $domainGame);

        $this->assertEquals(true, $game->finished());
        $this->assertEquals('player1', $game->abortedBy);
        $this->assertEquals('', $game->currentPlayerId);
    }

    /**
     * @test
     */
    public function itShouldBeMarkedAsFinishedWhenGameResigned(): void
    {
        $domainGame = DomainGame::open(GameId::generate(), Configuration::common(), 'player1');
        $domainGame->join('player2');
        $domainGame->move('player1', 1);
        $domainGame->move('player2', 1);
        $domainGame->resign('player1');

        $game = new Game();
        $this->applyFromDomainGame($game, $domainGame);

        $this->assertEquals(true, $game->finished());
        $this->assertEquals('player2', $game->winnerId);
        $this->assertEquals('player1', $game->resignedBy);
        $this->assertEquals('', $game->loserId);
        $this->assertEquals('', $game->currentPlayerId);
    }

    /**
     * @test
     */
    public function itShouldBeMarkedAsFinishedWhenGameWon(): void
    {
        $domainGame = DomainGame::open(GameId::generate(), Configuration::common(), 'player1');
        $domainGame->join('player2');
        $domainGame->move('player1', 1);
        $domainGame->move('player2', 2);
        $domainGame->move('player1', 1);
        $domainGame->move('player2', 2);
        $domainGame->move('player1', 1);
        $domainGame->move('player2', 2);
        $domainGame->move('player1', 1);

        $game = new Game();
        $this->applyFromDomainGame($game, $domainGame);

        $this->assertEquals(true, $game->finished());
        $this->assertEquals(
            [[
                'rule' => 'vertical',
                'points' => [['x' => 1, 'y' => 3], ['x' => 1, 'y' => 4], ['x' => 1, 'y' => 5], ['x' => 1, 'y' => 6]]
            ]],
            json_decode(json_encode($game), true)['winningSequences']
        );
        $this->assertEquals($game::STATE_FINISHED, $game->state);
        $this->assertEquals('player1', $game->winnerId);
        $this->assertEquals('player2', $game->loserId);
        $this->assertEquals('', $game->currentPlayerId);
    }

    /**
     * @test
     */
    public function itShouldBeMarkedAsDrawWhenGameDrawn(): void
    {
        $game = new Game();
        $game->apply(
            new GameDrawn(GameId::generate(), ['player1', 'player2'])
        );

        $this->assertEquals($game::STATE_DRAW, $game->state);
        $this->assertEquals('', $game->currentPlayerId);
    }

    private function applyFromDomainGame(Game $game, DomainGame $domainGame): void
    {
        foreach ($domainGame->flushDomainEvents() as $domainEvent) {
            // Apply twice to test idempotency.
            $game->apply($domainEvent);
            $game->apply($domainEvent);
        }
    }
}
