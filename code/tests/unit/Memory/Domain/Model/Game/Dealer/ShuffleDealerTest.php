<?php
declare(strict_types=1);

namespace Gaming\Memory\Domain\Model\Game\Dealer;

use PHPUnit\Framework\TestCase;

class ShuffleDealerTest extends TestCase
{
    /**
     * @test
     */
    public function shouldDealIn(): void
    {
        $dealer = new ShuffleDealer(3);

        $cardsWithoutDuplicates = array_unique($dealer->dealIn());

        $this->assertEquals(3, count($cardsWithoutDuplicates));
    }
}
