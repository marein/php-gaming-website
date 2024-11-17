<?php

declare(strict_types=1);

namespace Gaming\Common\Bus;

use Gaming\Common\Bus\Exception\BusException;
use Psr\Container\ContainerInterface;

/**
 * This routing bus does not have any runtime checks. They should happen somewhere else,
 * e.g. during container compilation.
 */
final class PsrCompiledRoutingBus implements Bus
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
