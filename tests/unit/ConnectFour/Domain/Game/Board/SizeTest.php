<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game\Board;

use Codeception\Attribute\DataProvider;
use Gaming\ConnectFour\Domain\Game\Board\Size;
use Gaming\ConnectFour\Domain\Game\Exception\InvalidSizeException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SizeTest extends TestCase
{
    #[Test]
    #[DataProvider('correctSizeProvider')]
    public function itShouldBeCreatedSuccessfully(int $width, int $height): void
    {
        $size = new Size($width, $height);

        $this->assertSame($width, $size->width());
        $this->assertSame($height, $size->height());
    }

    public function correctSizeProvider(): array
    {
        return [
            [4, 4],
            [7, 6],
            [5, 4],
            [10, 9]
        ];
    }

    #[Test]
    #[DataProvider('wrongSizeProvider')]
    public function itShouldThrowAnExceptionOnInvalidSizes(int $width, int $height): void
    {
        $this->expectException(InvalidSizeException::class);

        new Size($width, $height);
    }

    public function wrongSizeProvider(): array
    {
        return [
            [3, 3],
            [5, 5],
            [-1, 3],
            [2, -3],
            [-1, -3],
            [1, 1]
        ];
    }
}
