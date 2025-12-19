<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Common\ForkPool\Channel;

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
    public function itShouldSynchronizeWithTimeout(): void
    {
        $this->createSynchronizeChannels(100, 10)->synchronize(10);
    }

    #[Test]
    public function itShouldThrowOnSynchronizeFailure(): void
    {
        $this->expectException(ForkPoolException::class);

        $this->createSynchronizeChannels(100, receive: 'invalid')->synchronize();
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
}
