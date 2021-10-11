<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Memory\Domain\Model\Game\Dealer;

use Gaming\Memory\Domain\Model\Game\Dealer\ShuffleDealer;
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

        $this->assertCount(3, $cardsWithoutDuplicates);
    }
}
