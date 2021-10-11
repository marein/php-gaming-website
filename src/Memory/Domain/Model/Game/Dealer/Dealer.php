<?php

declare(strict_types=1);

namespace Gaming\Memory\Domain\Model\Game\Dealer;

interface Dealer
{
    /**
     * @return int[]
     */
    public function dealIn(): array;
}
