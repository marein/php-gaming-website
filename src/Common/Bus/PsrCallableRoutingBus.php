<?php

declare(strict_types=1);

namespace Gaming\Common\Bus;

use Gaming\Common\Bus\Exception\BusException;
use Psr\Container\ContainerInterface;

final class PsrCallableRoutingBus implements Bus
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    public function handle(Request $request): mixed
    {
        $handler = $this->container->has($request::class)
            ? $this->container->get($request::class)
            : throw BusException::missingHandler($request::class);

        return is_callable($handler)
            ? $handler($request)
            : throw BusException::missingHandler($request::class);
    }
}
