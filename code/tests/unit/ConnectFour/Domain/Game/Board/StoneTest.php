<?php
declare(strict_types=1);

namespace Gambling\ConnectFour\Domain\Game\Board;

use PHPUnit\Framework\TestCase;

class StoneTest extends TestCase
{
    /**
     * @test
     */
    public function aRedStoneCanBeCreated(): void
    {
        $this->assertSame(Stone::red()->color(), Stone::RED);
    }

    /**
     * @test
     */
    public function aYellowStoneCanBeCreated(): void
    {
        $this->assertSame(Stone::yellow()->color(), Stone::YELLOW);
    }

    /**
     * @test
     */
    public function aNoneStoneCanBeCreated(): void
    {
        $this->assertSame(Stone::none()->color(), Stone::NONE);
    }
}
