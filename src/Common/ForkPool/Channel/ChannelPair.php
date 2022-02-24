<?php

declare(strict_types=1);

namespace Gaming\Common\ForkPool\Channel;

final class ChannelPair
{
    public function __construct(
        private readonly Channel $parent,
        private readonly Channel $fork
    ) {
    }

    public function parent(): Channel
    {
        return $this->parent;
    }

    public function fork(): Channel
    {
        return $this->fork;
    }
}
