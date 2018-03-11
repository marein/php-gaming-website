<?php

namespace Gambling\Identity\Domain\Model\User;

use Gambling\Identity\Domain\Model\User\Event\UserArrived;
use Gambling\Identity\Domain\Model\User\Event\UserSignedUp;
use Gambling\Identity\Domain\Model\User\Exception\UserAlreadySignedUpException;
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
    public function itShouldSignUp(): void
    {
        $expectedCredentials = new Credentials(
            'marein',
            'password'
        );

        $user = User::arrive();
        $user->signUp($expectedCredentials);

        $domainEvents = $user->flushDomainEvents();
        $userSignedUp = $domainEvents[1];

        $this->assertEquals($expectedCredentials, $user->credentials());
        $this->assertCount(2, $domainEvents);
        $this->assertInstanceOf(UserSignedUp::class, $userSignedUp);
        $this->assertEquals($user->id()->toString(), $userSignedUp->aggregateId());
        $this->assertEquals($expectedCredentials->username(), $userSignedUp->payload()['username']);
    }

    /**
     * @test
     */
    public function itShouldThrowAnExceptionIfAlreadySignedUp(): void
    {
        $this->expectException(UserAlreadySignedUpException::class);

        $credentials = new Credentials('marein', 'password');

        $user = User::arrive();
        $user->signUp($credentials);
        $user->signUp($credentials);
    }
}
