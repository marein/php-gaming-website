<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game\Board;

use Codeception\Attribute\DataProvider;
use Gaming\Common\Domain\Test\DomainAssert;
use Gaming\ConnectFour\Domain\Game\Board\Size;
use Gaming\ConnectFour\Domain\Game\Exception\SizeProductNotEvenException;
use Gaming\ConnectFour\Domain\Game\Exception\SizeTooSmallException;
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
    public function itShouldThrowAnExceptionOnInvalidSizes(
        int $width,
        int $height,
        string $expectedException,
        string $expectedIdentifier
    ): void {
        DomainAssert::expectViolation(
            fn() => new Size($width, $height),
            $expectedException,
            $expectedIdentifier,
            ['width' => $width, 'height' => $height]
        );
    }

    public function wrongSizeProvider(): array
    {
        return [
            [3, 3, SizeProductNotEvenException::class, 'invalid_size_not_even'],
            [5, 5, SizeProductNotEvenException::class, 'invalid_size_not_even'],
            [-1, 3, SizeTooSmallException::class, 'invalid_size_too_small'],
            [2, -3, SizeTooSmallException::class, 'invalid_size_too_small'],
            [-1, -3, SizeTooSmallException::class, 'invalid_size_too_small'],
            [1, 1, SizeTooSmallException::class, 'invalid_size_too_small']
        ];
    }
}
