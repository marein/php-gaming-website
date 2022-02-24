<?php

declare(strict_types=1);

namespace Gaming\Common\ForkControl\Channel;

use Gaming\Common\ForkControl\Exception\ForkControlException;

interface ChannelPairFactory
{
    /**
     * @throws ForkControlException
     */
    public function create(): ChannelPair;
}
