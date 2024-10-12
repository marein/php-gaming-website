<?php

declare(strict_types=1);

namespace Gaming\Common\Bus\Integration\Bundle;

use Gaming\Common\Bus\Bus;
use Gaming\Common\Bus\Exception\BusException;
use Gaming\Common\Bus\Request;
use Psr\Container\ContainerInterface;

final class RoutingBus implements Bus
{
    /**
     * @param array<class-string, array{handlerId: string, method: string}> $routes
     */
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly array $routes
    ) {
    }

    public function handle(Request $request): mixed
    {
        $route = $this->routes[$request::class] ?? throw BusException::missingHandler($request::class);

        return $this->container->get($route['handlerId'])->{$route['method']}($request);
    }
}
