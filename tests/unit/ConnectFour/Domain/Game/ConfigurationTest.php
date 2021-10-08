<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game;

use Gaming\ConnectFour\Domain\Game\Board\Size;
use Gaming\ConnectFour\Domain\Game\Configuration;
use Gaming\ConnectFour\Domain\Game\WinningRule\CommonWinningRule;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldBeCreatedWithCommonConfiguration(): void
    {
        $configuration = Configuration::common();

        $this->assertEquals(7, $configuration->size()->width());
        $this->assertEquals(6, $configuration->size()->height());
        $this->assertInstanceOf(CommonWinningRule::class, $configuration->winningRule());
    }

    /**
     * @test
     */
    public function itShouldBeCreatedWithCustomConfiguration(): void
    {
        $configuration = Configuration::custom(
            new Size(7, 6),
            new CommonWinningRule()
        );

        $this->assertEquals(7, $configuration->size()->width());
        $this->assertEquals(6, $configuration->size()->height());
        $this->assertInstanceOf(CommonWinningRule::class, $configuration->winningRule());
    }
}
