<?php

declare(strict_types=1);

namespace Gaming\Common\Bus;

use Gaming\Common\Bus\Exception\BusException;

final class RoutingBus implements Bus
{
    /**
     * @param callable[] $requestToHandlerMap
     */
    public function __construct(
        private readonly array $requestToHandlerMap
    ) {
    }

    public function handle(Request $request): mixed
    {
        return ($this->requestToHandlerMap[$request::class] ?? throw BusException::missingHandler($request::class))(
            $request
        );
    }
}
