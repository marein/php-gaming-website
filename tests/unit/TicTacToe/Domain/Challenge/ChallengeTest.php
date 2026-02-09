<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\TicTacToe\Domain\Challenge;

use Gaming\Common\EventStore\DomainEvent;
use Gaming\Common\EventStore\DomainEvents;
use Gaming\Common\Domain\Test\DomainAssert;
use Gaming\TicTacToe\Domain\Challenge\Challenge;
use Gaming\TicTacToe\Domain\Challenge\ChallengeId;
use Gaming\TicTacToe\Domain\Challenge\Event\ChallengeAccepted;
use Gaming\TicTacToe\Domain\Challenge\Event\ChallengeOpened;
use Gaming\TicTacToe\Domain\Challenge\Event\ChallengeWithdrawn;
use Gaming\TicTacToe\Domain\Challenge\Exception\ChallengeException;
use Gaming\TicTacToe\Domain\Game\Configuration;
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
                new ChallengeOpened($challengeId->toString(), 3, null, 'move:15000', 'player1'),
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
        $challenge = $this->createOpenChallenge('player1');

        DomainAssert::expectViolation(
            fn() => $challenge->withdraw('player2'),
            ChallengeException::class,
            'only_challenger_can_withdraw'
        );
    }

    #[Test]
    public function cannotWithdrawAlreadyWithdrawnChallenge(): void
    {
        $challenge = $this->createOpenChallenge('player1');
        $challenge->withdraw('player1');

        DomainAssert::expectViolation(
            fn() => $challenge->withdraw('player1'),
            ChallengeException::class,
            'challenge_already_closed'
        );
    }

    #[Test]
    public function cannotWithdrawAcceptedChallenge(): void
    {
        $challenge = $this->createOpenChallenge('player1');
        $challenge->accept('player2');

        DomainAssert::expectViolation(
            fn() => $challenge->withdraw('player1'),
            ChallengeException::class,
            'challenge_already_closed'
        );
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
        $challenge = $this->createOpenChallenge('player1');

        DomainAssert::expectViolation(
            fn() => $challenge->accept('player1'),
            ChallengeException::class,
            'cannot_accept_own_challenge'
        );
    }

    #[Test]
    public function cannotAcceptAlreadyAcceptedChallenge(): void
    {
        $challenge = $this->createOpenChallenge('player1');
        $challenge->accept('player2');

        DomainAssert::expectViolation(
            fn() => $challenge->accept('another-player2'),
            ChallengeException::class,
            'challenge_already_closed'
        );
    }

    #[Test]
    public function cannotAcceptWithdrawnChallenge(): void
    {
        $challenge = $this->createOpenChallenge('player1');
        $challenge->withdraw('player1');

        DomainAssert::expectViolation(
            fn() => $challenge->accept('player2'),
            ChallengeException::class,
            'challenge_already_closed'
        );
    }

    private function createOpenChallenge(string $challengerId): Challenge
    {
        $challenge = Challenge::open(ChallengeId::generate(), $challengerId, Configuration::common());

        $this->assertEquals($challenge->flushDomainEvents(), [
            new DomainEvent(
                $challenge->challengeId->toString(),
                new ChallengeOpened($challenge->challengeId->toString(), 3, null, 'move:15000', $challengerId),
                1
            )
        ]);

        return $challenge;
    }
}
