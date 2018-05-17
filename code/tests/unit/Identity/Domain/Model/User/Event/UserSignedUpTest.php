<?php

namespace Gambling\Identity\Domain\Model\User\Event;

use Gambling\Common\Clock\Clock;
use Gambling\Identity\Domain\Model\User\UserId;
use PHPUnit\Framework\TestCase;

final class UserSignedUpTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldBeCreatedWithItsValues(): void
    {
        Clock::instance()->freeze();

        $userId = UserId::generate();
        $username = 'luke';
        $payload = [
            'userId'   => $userId->toString(),
            'username' => $username
        ];

        $userSignedUp = new UserSignedUp($userId, $username);

        $this->assertSame('UserSignedUp', $userSignedUp->name());
        $this->assertSame($userId->toString(), $userSignedUp->aggregateId());
        $this->assertSame(Clock::instance()->now(), $userSignedUp->occurredOn());
        $this->assertSame($payload, $userSignedUp->payload());

        Clock::instance()->resume();
    }
}
