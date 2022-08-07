<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game\Board;

use Gaming\ConnectFour\Domain\Game\Board\Stone;
use PHPUnit\Framework\TestCase;

class StoneTest extends TestCase
{
    /**
     * @test
     */
    public function aNoneStoneCanBeCreated(): void
    {
        $this->assertSame(0, Stone::None->color());
    }

    /**
     * @test
     */
    public function aRedStoneCanBeCreated(): void
    {
        $this->assertSame(1, Stone::Red->color());
    }

    /**
     * @test
     */
    public function aYellowStoneCanBeCreated(): void
    {
        $this->assertSame(2, Stone::Yellow->color());
    }
}
