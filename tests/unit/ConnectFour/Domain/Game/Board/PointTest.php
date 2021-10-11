<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game\Board;

use Gaming\ConnectFour\Domain\Game\Board\Point;
use PHPUnit\Framework\TestCase;

class PointTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldBeCreatedWithItsValues(): void
    {
        $point = new Point(3, 4);

        $this->assertSame($point->x(), 3);
        $this->assertSame($point->y(), 4);
    }
}
