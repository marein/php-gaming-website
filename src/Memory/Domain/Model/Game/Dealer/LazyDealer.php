<?php

declare(strict_types=1);

namespace Gaming\Memory\Domain\Model\Game\Dealer;

/**
 * The lazy dealer deals the cards in sorted order. Perfect for unit testing.
 */
final class LazyDealer implements Dealer
{
    private int $numberOfPairs;

    public function __construct(int $numberOfPairs)
    {
        $this->numberOfPairs = $numberOfPairs;
    }

    public function dealIn(): array
    {
        $cards = array_merge(
            range(1, $this->numberOfPairs),
            range(1, $this->numberOfPairs)
        );

        sort($cards);

        return $cards;
    }
}
