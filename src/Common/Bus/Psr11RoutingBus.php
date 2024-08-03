<?php

declare(strict_types=1);

namespace Gaming\Common\Bus;

use Gaming\Common\Bus\Exception\BusException;
use Psr\Container\ContainerInterface;

final class Psr11RoutingBus implements Bus
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    public function handle(Request $request): mixed
    {
        return $this->container->has($request::class)
            ? $this->container->get($request::class)($request)
            : throw BusException::missingHandler($request::class);
    }
}
