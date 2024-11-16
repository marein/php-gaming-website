<?php

declare(strict_types=1);

namespace Gaming\Common\Bus;

use Closure;
use Gaming\Common\Bus\Exception\BusException;
use Psr\Container\ContainerInterface;

final class PsrRuntimeRoutingBus implements Bus
{
    /**
     * @var array<class-string, Closure(Request<mixed>): mixed>
     */
    private array $handlers = [];

    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    public function handle(Request $request): mixed
    {
        return ($this->handlers[$request::class] ??= $this->discoverHandler($request))($request);
    }

    /**
     * @param Request<mixed> $request
     *
     * @return Closure(Request<mixed>): mixed
     */
    private function discoverHandler(Request $request): Closure
    {
        $handler = $this->container->has($request::class)
            ? $this->container->get($request::class)
            : throw BusException::missingHandler($request::class);

        foreach ((new HandlerDiscovery())->forClass($handler::class) as $type => $method) {
            if ($request instanceof $type) {
                return static fn(Request $request) => $handler->{$method}($request);
            }
        }

        throw BusException::missingHandler($request::class);
    }
}
