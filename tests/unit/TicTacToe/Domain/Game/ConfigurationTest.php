<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\TicTacToe\Domain\Game;

use Codeception\Attribute\DataProvider;
use Gaming\Common\Domain\Test\DomainAssert;
use Gaming\Common\Timer\MoveTimer;
use Gaming\TicTacToe\Domain\Game\Configuration;
use Gaming\TicTacToe\Domain\Game\Exception\SizeOutOfRangeException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    #[Test]
    public function itCanBeCreated(): void
    {
        $configuration = new Configuration(3, null, MoveTimer::set(15000));

        $this->assertSame(3, $configuration->size);
        $this->assertNull($configuration->preferredToken);
        $this->assertEquals(MoveTimer::set(15000), $configuration->timer);
    }

    #[Test]
    #[DataProvider('invalidSizeProvider')]
    public function itShouldThrowSizeOutOfRangeExceptionOnInvalidSize(int $size): void
    {
        DomainAssert::expectViolation(
            fn() => new Configuration($size, null, MoveTimer::set(15000)),
            SizeOutOfRangeException::class,
            'size_out_of_range',
            ['min' => 3, 'max' => 9, 'value' => $size]
        );
    }

    /**
     * Returns data for itShouldThrowSizeOutOfRangeExceptionOnInvalidSize
     */
    public function invalidSizeProvider(): array
    {
        return [
            [2],
            [10]
        ];
    }
}
