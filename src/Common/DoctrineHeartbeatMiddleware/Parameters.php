<?php

declare(strict_types=1);

namespace Gaming\Common\DoctrineHeartbeatMiddleware;

final class Parameters
{
    public const HEARTBEAT = 'heartbeat';

    /**
     * @param array<mixed> $parameters
     */
    public function __construct(
        public readonly array $parameters
    ) {
    }

    public function heartbeat(): int
    {
        return (int)(
            $this->parameters[self::HEARTBEAT] ?? $this->parameters['driverOptions'][self::HEARTBEAT] ?? 0
        );
    }

    public function removeDriverOptions(): self
    {
        $parameters = $this->parameters;
        unset($parameters[self::HEARTBEAT]);
        unset($parameters['driverOptions'][self::HEARTBEAT]);

        return new Parameters($parameters);
    }
}
