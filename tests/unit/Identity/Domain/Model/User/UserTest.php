<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Identity\Domain\Model\User;

use Gaming\Identity\Domain\Model\User\Credentials;
use Gaming\Identity\Domain\Model\User\Event\UserArrived;
use Gaming\Identity\Domain\Model\User\Event\UserSignedUp;
use Gaming\Identity\Domain\Model\User\Exception\UserAlreadySignedUpException;
use Gaming\Identity\Domain\Model\User\User;
use Gaming\Identity\Domain\Model\User\UserId;
use Gaming\Identity\Port\Adapter\HashAlgorithm\NotSecureHashAlgorithm;
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

        assert($domainEvents[0] instanceof UserArrived);
        self::assertEquals($userId->toString(), $domainEvents[0]->aggregateId());
    }

    /**
     * @test
     */
    public function itShouldSignUpAndAuthenticate(): void
    {
        $hashAlgorithm = new NotSecureHashAlgorithm();

        $userId = UserId::generate();
        $user = User::arrive($userId);
        $user->signUp(
            new Credentials(
                'marein',
                'correctPassword',
                $hashAlgorithm
            )
        );

        $domainEvents = $user->flushDomainEvents();
        self::assertCount(2, $domainEvents);

        assert($domainEvents[1] instanceof UserSignedUp);
        self::assertEquals($userId->toString(), $domainEvents[1]->aggregateId());
        self::assertEquals('marein', $domainEvents[1]->username());

        $this->assertTrue($user->authenticate('correctPassword', $hashAlgorithm));
        $this->assertFalse($user->authenticate('wrongPassword', $hashAlgorithm));
    }

    /**
     * @test
     */
    public function itShouldNotAuthenticateWhenNotSignedUp(): void
    {
        $user = User::arrive(
            UserId::generate()
        );
        $authenticate = $user->authenticate('password', new NotSecureHashAlgorithm());

        self::assertFalse($authenticate);
    }

    /**
     * @test
     */
    public function itShouldThrowAnExceptionIfAlreadySignedUp(): void
    {
        $this->expectException(UserAlreadySignedUpException::class);

        $credentials = new Credentials(
            'marein',
            'password',
            new NotSecureHashAlgorithm()
        );

        $user = User::arrive(
            UserId::generate()
        );
        $user->signUp($credentials);
        $user->signUp($credentials);
    }
}
