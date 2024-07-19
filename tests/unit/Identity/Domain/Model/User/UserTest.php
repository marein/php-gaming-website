<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Identity\Domain\Model\User;

use Gaming\Identity\Domain\Model\User\Event\UserArrived;
use Gaming\Identity\Domain\Model\User\Event\UserSignedUp;
use Gaming\Identity\Domain\Model\User\Exception\UserAlreadySignedUpException;
use Gaming\Identity\Domain\Model\User\User;
use Gaming\Identity\Domain\Model\User\UserId;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldArrive(): void
    {
        $userId = UserId::generate();
        $user = User::arrive($userId);

        $domainEvents = $user->flushDomainEvents();
        self::assertCount(1, $domainEvents);

        assert($domainEvents[0]->content instanceof UserArrived);
        self::assertEquals($userId->toString(), $domainEvents[0]->content->aggregateId());
    }

    /**
     * @test
     */
    public function itShouldSignUp(): void
    {
        $userId = UserId::generate();
        $user = User::arrive($userId);
        $user->signUp('marein@example.com', 'marein');

        $domainEvents = $user->flushDomainEvents();
        self::assertCount(2, $domainEvents);

        assert($domainEvents[1]->content instanceof UserSignedUp);
        self::assertEquals($userId->toString(), $domainEvents[1]->content->aggregateId());
        self::assertEquals('marein@example.com', $domainEvents[1]->content->email);
        self::assertEquals('marein', $domainEvents[1]->content->username);
    }

    /**
     * @test
     */
    public function itShouldThrowAnExceptionIfAlreadySignedUp(): void
    {
        $this->expectException(UserAlreadySignedUpException::class);

        $user = User::arrive(UserId::generate());
        $user->signUp('marein@example.com', 'marein');
        $user->signUp('any@example.com', 'any');
    }
}
