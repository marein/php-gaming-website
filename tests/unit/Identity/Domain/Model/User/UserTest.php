<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Identity\Domain\Model\User;

use Gaming\Identity\Domain\Model\Account\AccountId;
use Gaming\Identity\Domain\Model\User\Event\UserArrived;
use Gaming\Identity\Domain\Model\User\Event\UserSignedUp;
use Gaming\Identity\Domain\Model\User\Exception\UserAlreadySignedUpException;
use Gaming\Identity\Domain\Model\User\User;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    #[Test]
    public function itShouldArrive(): void
    {
        $userId = AccountId::generate();
        $user = User::arrive($userId);

        $domainEvents = $user->flushDomainEvents();
        self::assertCount(1, $domainEvents);

        assert($domainEvents[0]->content instanceof UserArrived);
        self::assertEquals($userId->toString(), $domainEvents[0]->content->aggregateId());
    }

    #[Test]
    public function itShouldSignUp(): void
    {
        $userId = AccountId::generate();
        $user = User::arrive($userId);
        $user->signUp('marein@example.com', 'marein');

        $domainEvents = $user->flushDomainEvents();
        self::assertCount(2, $domainEvents);

        assert($domainEvents[1]->content instanceof UserSignedUp);
        self::assertEquals($userId->toString(), $domainEvents[1]->content->aggregateId());
        self::assertEquals('marein@example.com', $domainEvents[1]->content->email);
        self::assertEquals('marein', $domainEvents[1]->content->username);
    }

    #[Test]
    public function itShouldThrowAnExceptionIfAlreadySignedUp(): void
    {
        $this->expectException(UserAlreadySignedUpException::class);

        $user = User::arrive(AccountId::generate());
        $user->signUp('marein@example.com', 'marein');
        $user->signUp('any@example.com', 'any');
    }
}
