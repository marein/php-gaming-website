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
        $response = $this->bus->handle($request);

        $this->manager->clear();

        return $response;
    }
}
