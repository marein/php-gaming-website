<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\TicTacToe\Domain\Challenge;

use Gaming\Common\EventStore\DomainEvent;
use Gaming\Common\EventStore\DomainEvents;
use Gaming\TicTacToe\Domain\Challenge\Challenge;
use Gaming\TicTacToe\Domain\Challenge\ChallengeId;
use Gaming\TicTacToe\Domain\Challenge\Event\ChallengeAccepted;
use Gaming\TicTacToe\Domain\Challenge\Event\ChallengeOpened;
use Gaming\TicTacToe\Domain\Challenge\Event\ChallengeWithdrawn;
use Gaming\TicTacToe\Domain\Challenge\Exception\CannotAcceptOwnChallengeException;
use Gaming\TicTacToe\Domain\Challenge\Exception\CannotWithdrawException;
use Gaming\TicTacToe\Domain\Challenge\Exception\NotOpenException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ChallengeTest extends TestCase
{
    #[Test]
    public function itCanBeOpened(): void
    {
        $this->createOpenChallenge('player1');
    }

    #[Test]
    public function itCanBeContinued(): void
    {
        $challengeId = ChallengeId::generate();

        $challenge = Challenge::fromHistory($challengeId, new DomainEvents($challengeId->toString(), 1, [
            new DomainEvent(
                $challengeId->toString(),
                new ChallengeOpened($challengeId->toString(), 3, 3, 'player1'),
                1
            )
        ]));

        $this->assertCount(0, $challenge->flushDomainEvents());

        $challenge->accept('player2');

        $this->assertEquals($challenge->flushDomainEvents(), [
            new DomainEvent(
                $challengeId->toString(),
                new ChallengeAccepted($challengeId->toString(), 'player1', 'player2'),
                2
            )
        ]);
    }

    #[Test]
    public function challengerCanWithdrawTheirChallenge(): void
    {
        $challenge = $this->createOpenChallenge('player1');

        $challenge->withdraw('player1');

        $this->assertEquals($challenge->flushDomainEvents(), [
            new DomainEvent(
                $challenge->challengeId->toString(),
                new ChallengeWithdrawn($challenge->challengeId->toString(), 'player1'),
                2
            )
        ]);
    }

    #[Test]
    public function onlyChallengerCanWithdrawChallenge(): void
    {
        $this->expectException(CannotWithdrawException::class);

        $challenge = $this->createOpenChallenge('player1');

        $challenge->withdraw('player2');
    }

    #[Test]
    public function cannotWithdrawAlreadyWithdrawnChallenge(): void
    {
        $this->expectException(NotOpenException::class);

        $challenge = $this->createOpenChallenge('player1');
        $challenge->withdraw('player1');

        $challenge->withdraw('player1');
    }

    #[Test]
    public function cannotWithdrawAcceptedChallenge(): void
    {
        $this->expectException(NotOpenException::class);

        $challenge = $this->createOpenChallenge('player1');
        $challenge->accept('player2');

        $challenge->withdraw('player1');
    }

    #[Test]
    public function playersCanAcceptChallenge(): void
    {
        $challenge = $this->createOpenChallenge('player1');

        $challenge->accept('player2');

        $this->assertEquals($challenge->flushDomainEvents(), [
            new DomainEvent(
                $challenge->challengeId->toString(),
                new ChallengeAccepted($challenge->challengeId->toString(), 'player1', 'player2'),
                2
            )
        ]);
    }

    #[Test]
    public function challengerCannotAcceptOwnChallenge(): void
    {
        $this->expectException(CannotAcceptOwnChallengeException::class);

        $challenge = $this->createOpenChallenge('player1');

        $challenge->accept('player1');
    }

    #[Test]
    public function cannotAcceptAlreadyAcceptedChallenge(): void
    {
        $this->expectException(NotOpenException::class);

        $challenge = $this->createOpenChallenge('player1');
        $challenge->accept('player2');

        $challenge->accept('another-player2');
    }

    #[Test]
    public function cannotAcceptWithdrawnChallenge(): void
    {
        $this->expectException(NotOpenException::class);

        $challenge = $this->createOpenChallenge('player1');
        $challenge->withdraw('player1');

        $challenge->accept('player2');
    }

    private function createOpenChallenge(string $challengerId): Challenge
    {
        $challenge = Challenge::open(ChallengeId::generate(), $challengerId);

        $this->assertEquals($challenge->flushDomainEvents(), [
            new DomainEvent(
                $challenge->challengeId->toString(),
                new ChallengeOpened($challenge->challengeId->toString(), 3, 3, $challengerId),
                1
            )
        ]);

        return $challenge;
    }
}
