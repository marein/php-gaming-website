<?php

declare(strict_types=1);

namespace Gaming\Common\Bus;

use Exception;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * This class can be used for tests and container-less applications.
 */
final class TestContainer implements ContainerInterface
{
    /**
     * @param array<string, mixed> $services
     */
    public function __construct(
        private array $services = []
    ) {
    }

    public function set(string $id, mixed $service): self
    {
        $this->services[$id] = $service;

        return $this;
    }

    public function get(string $id)
    {
        return $this->services[$id] ?? throw new class ($id) extends Exception implements NotFoundExceptionInterface {
            public function __construct(
                public readonly string $serviceId
            ) {
                parent::__construct('Service with id "' . $serviceId . '" not found.');
            }
        };
    }

    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }
}
