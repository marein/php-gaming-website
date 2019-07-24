<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Board;

use PHPUnit\Framework\TestCase;

class StoneTest extends TestCase
{
    /**
     * @test
     */
    public function aNoneStoneCanBeCreated(): void
    {
        $this->assertSame(0, Stone::none()->color());
    }

    /**
     * @test
     */
    public function aRedStoneCanBeCreated(): void
    {
        $this->assertSame(1, Stone::red()->color());
    }

    /**
     * @test
     */
    public function aYellowStoneCanBeCreated(): void
    {
        $this->assertSame(2, Stone::yellow()->color());
    }
}
