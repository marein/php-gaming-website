<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Common\ForkPool\Channel;

use Exception;
use Gaming\Common\ForkPool\Channel\Channel;
use Gaming\Common\ForkPool\Channel\Channels;
use Gaming\Common\ForkPool\Exception\ForkPoolException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ChannelsTest extends TestCase
{
    #[Test]
    public function itShouldThrowWhenEmpty(): void
    {
        $this->expectException(ForkPoolException::class);

        new Channels([]);
    }

    #[Test]
    public function itShouldReturnRandomChannels(): void
    {
        $channels = $this->createChannels(100);

        for ($i = 0; $i < 100; $i++) {
            $this->assertContains($channels->random()->receive(), range(1, 100));
        }
    }

    #[Test]
    public function itShouldReturnRoundRobinChannels(): void
    {
        $channels = $this->createChannels($count = 100);

        for ($i = 1; $i <= $count * 3; $i++) {
            $this->assertSame($channels->roundRobin()->receive(), $i % $count === 0 ? $count : $i % $count);
        }
    }

    #[Test]
    public function itShouldReturnConsistentChannels(): void
    {
        $keys = ['key1' => 2, 'key2' => 4, 'key3' => 1, 'key4' => 4, 'key5' => 1];
        $channels = $this->createChannels(5);

        for ($i = 0; $i < 100; $i++) {
            foreach ($keys as $key => $expectedReceive) {
                $this->assertSame($channels->consistent($key)->receive(), $expectedReceive);
            }
        }
    }

    #[Test]
    public function itShouldSynchronize(): void
    {
        $this->createSynchronizeChannels(100)->synchronize();
    }

    #[Test]
    public function itShouldThrowOnSynchronizeWithInvalidMessage(): void
    {
        $this->expectException(ForkPoolException::class);

        $this->createSynchronizeChannels(100, receive: 'invalid')->synchronize();
    }

    #[Test]
    public function itShouldThrowOnSynchronizeWhenTimeout(): void
    {
        $called1 = $called2 = $called3 = $called4 = $called5 = false;

        try {
            // Passing a clock just to fake timeouts for this little test doesn’t feel right.
            // Using sleep with max 1s instead.
            new Channels(
                [
                    $this->createSleepingChannel($called1, 1, 360), // This times out, but succeeds in the last second.
                    $this->createSleepingChannel($called2), // This succeeds immediately.
                    $this->createSleepingChannel($called3), // This too.
                    $this->createSleepingChannel($called4, 0, 360, false), // This actually times out.
                    $this->createSleepingChannel($called5) // This is never called.
                ]
            )->synchronize(1);
        } catch (ForkPoolException) {
            $this->assertSame([true, true, true, true], [$called1, $called2, $called3, $called4]);
            $this->assertSame(false, $called5);

            return;
        }

        $this->fail(__FUNCTION__ . ' did not throw an exception.');
    }

    private function createChannels(int $count): Channels
    {
        $channels = [];
        for ($i = 1; $i <= $count; $i++) {
            $channel = $this->createMock(Channel::class);
            $channel->method('receive')->willReturn($i);
            $channels[] = $channel;
        }

        return new Channels($channels);
    }

    private function createSynchronizeChannels(
        int $count,
        ?int $timeout = null,
        string $receive = Channel::MESSAGE_SYNC_ACK
    ): Channels {
        $channels = [];
        for ($i = 1; $i <= $count; $i++) {
            $channel = $this->createMock(Channel::class);
            $channel->method('send')->with(Channel::MESSAGE_SYNC);
            $channel->method('receive')->with($timeout)->willReturn($receive);
            $channels[] = $channel;
        }

        return new Channels($channels);
    }

    private function createSleepingChannel(
        bool &$called,
        int $withTimeout = 0,
        ?int $sleepSeconds = null,
        bool $success = true
    ): Channel {
        return new class ($called, $withTimeout, $sleepSeconds, $success) implements Channel {
            public function __construct(
                private bool &$called,
                private int $withTimeout,
                private readonly ?int $sleepSeconds,
                private readonly bool $success,
            ) {
            }

            public function send(mixed $message): void
            {
            }

            public function receive(?int $timeout = null): ?string
            {
                $this->called = true;

                if ($this->withTimeout !== $timeout) {
                    throw new Exception('Unexpected timeout value.');
                }

                if ($timeout !== null && $this->sleepSeconds !== null) {
                    usleep(min($this->sleepSeconds, $timeout) * 1000000 + 100000); // Extra 0.1s to be sure.
                }

                return $this->success ? Channel::MESSAGE_SYNC_ACK : null;
            }
        };
    }
}
