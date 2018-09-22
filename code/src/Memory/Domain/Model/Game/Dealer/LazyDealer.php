<?php
declare(strict_types=1);

namespace Gambling\Memory\Domain\Model\Game\Dealer;

/**
 * The lazy dealer deals the cards in sorted order. Perfect for unit testing.
 */
final class LazyDealer implements Dealer
{
    /**
     * @var int
     */
    private $numberOfPairs;

    /**
     * ShuffleDealer constructor.
     *
     * @param int $numberOfPairs
     */
    public function __construct($numberOfPairs)
    {
        $this->numberOfPairs = $numberOfPairs;
    }

    /**
     * @inheritdoc
     */
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
