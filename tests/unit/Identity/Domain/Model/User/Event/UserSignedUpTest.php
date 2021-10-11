<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Identity\Domain\Model\User\Event;

use Gaming\Common\Clock\Clock;
use Gaming\Identity\Domain\Model\User\Event\UserSignedUp;
use Gaming\Identity\Domain\Model\User\UserId;
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
            'userId' => $userId->toString(),
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
