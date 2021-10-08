<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Identity\Domain\Model\User;

use Gaming\Identity\Domain\Model\User\Credentials;
use Gaming\Identity\Domain\Model\User\Event\UserArrived;
use Gaming\Identity\Domain\Model\User\Event\UserSignedUp;
use Gaming\Identity\Domain\Model\User\Exception\UserAlreadySignedUpException;
use Gaming\Identity\Domain\Model\User\User;
use Gaming\Identity\Port\Adapter\HashAlgorithm\NotSecureHashAlgorithm;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldArrive(): void
    {
        $user = User::arrive();

        $domainEvents = $user->flushDomainEvents();
        $userArrived = $domainEvents[0];

        $this->assertCount(1, $domainEvents);
        $this->assertInstanceOf(UserArrived::class, $userArrived);
        $this->assertEquals($user->id()->toString(), $userArrived->aggregateId());
    }

    /**
     * @test
     */
    public function itShouldSignUpAndAuthenticate(): void
    {
        $hashAlgorithm = new NotSecureHashAlgorithm();

        $user = User::arrive();
        $user->signUp(
            new Credentials(
                'marein',
                'correctPassword',
                $hashAlgorithm
            )
        );

        $domainEvents = $user->flushDomainEvents();
        $userSignedUp = $domainEvents[1];

        $this->assertCount(2, $domainEvents);
        $this->assertInstanceOf(UserSignedUp::class, $userSignedUp);
        $this->assertEquals($user->id()->toString(), $userSignedUp->aggregateId());
        $this->assertEquals('marein', $userSignedUp->payload()['username']);

        $this->assertTrue($user->authenticate('correctPassword', $hashAlgorithm));
        $this->assertFalse($user->authenticate('wrongPassword', $hashAlgorithm));
    }

    /**
     * @test
     */
    public function itShouldNotAuthenticateWhenNotSignedUp(): void
    {
        $user = User::arrive();
        $authenticate = $user->authenticate('password', new NotSecureHashAlgorithm());

        $this->assertFalse($authenticate);
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

        $user = User::arrive();
        $user->signUp($credentials);
        $user->signUp($credentials);
    }
}
