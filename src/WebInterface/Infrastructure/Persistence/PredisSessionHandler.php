<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Persistence;

use Predis\ClientInterface;
use SessionHandlerInterface;

/**
 * There is no locking mechanism implemented.
 */
final class PredisSessionHandler implements SessionHandlerInterface
{
    private ClientInterface $predis;

    private string $keyPrefix;

    private int $lifetime;

    public function __construct(ClientInterface $predis, string $keyPrefix, int $lifetime)
    {
        $this->predis = $predis;
        $this->keyPrefix = $keyPrefix;
        $this->lifetime = $lifetime;
    }

    public function close(): bool
    {
        return true;
    }

    public function destroy($sessionId): bool
    {
        $this->predis->del(
            $this->generateKey($sessionId)
        );

        return true;
    }

    public function gc($maxlifetime): bool
    {
        return true;
    }

    public function open($savePath, $name): bool
    {
        return true;
    }

    public function read($sessionId): string
    {
        return (string)$this->predis->get(
            $this->generateKey($sessionId)
        );
    }

    public function write($sessionId, $sessionData): bool
    {
        $this->predis->setex(
            $this->generateKey($sessionId),
            $this->lifetime,
            $sessionData
        );

        return true;
    }

    private function generateKey(string $key): string
    {
        return $this->keyPrefix . $key;
    }
}
