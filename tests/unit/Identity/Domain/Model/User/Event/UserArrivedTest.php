<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Identity\Domain\Model\User\Event;

use Gaming\Common\Clock\Clock;
use Gaming\Identity\Domain\Model\User\Event\UserArrived;
use Gaming\Identity\Domain\Model\User\UserId;
use PHPUnit\Framework\TestCase;

final class UserArrivedTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldBeCreatedWithItsValues(): void
    {
        Clock::instance()->freeze();

        $userId = UserId::generate();
        $payload = [
            'userId' => $userId->toString()
        ];

        $userArrived = new UserArrived($userId);

        $this->assertSame('UserArrived', $userArrived->name());
        $this->assertSame($userId->toString(), $userArrived->aggregateId());
        $this->assertSame(Clock::instance()->now(), $userArrived->occurredOn());
        $this->assertSame($payload, $userArrived->payload());

        Clock::instance()->resume();
    }
}
