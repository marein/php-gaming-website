<?php
declare(strict_types=1);

namespace Gambling\Memory\Domain\Model\Game\Dealer;

use PHPUnit\Framework\TestCase;

class LazyDealerTest extends TestCase
{
    /**
     * @test
     */
    public function shouldDealIn(): void
    {
        $expectedCards = [1, 1, 2, 2, 3, 3];

        $dealer = new LazyDealer(3);

        $this->assertEquals($expectedCards, $dealer->dealIn());
    }
}
