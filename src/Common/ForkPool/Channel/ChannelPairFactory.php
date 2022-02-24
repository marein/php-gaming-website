<?php

declare(strict_types=1);

namespace Gaming\Common\ForkPool\Channel;

use Gaming\Common\ForkPool\Exception\ForkPoolException;

interface ChannelPairFactory
{
    /**
     * @throws ForkPoolException
     */
    public function create(): ChannelPair;
}
