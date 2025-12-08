<?php

declare(strict_types=1);

namespace Gaming\Common\Bus\Integration;

use Doctrine\Persistence\ObjectManager;
use Gaming\Common\Bus\Bus;
use Gaming\Common\Bus\Request;

final class DoctrineClearManagerBus implements Bus
{
    public function __construct(
        private readonly Bus $bus,
        private readonly ObjectManager $manager
    ) {
    }

    public function handle(Request $request): mixed
    {
        try {
            return $this->bus->handle($request);
        } finally {
            $this->manager->clear();
        }
    }
}
