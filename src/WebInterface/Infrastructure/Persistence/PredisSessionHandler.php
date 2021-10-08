<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Infrastructure\Persistence;

use Predis\ClientInterface;
use SessionHandlerInterface;

/**
 * Class PredisSessionHandler
 *
 * There is no locking mechanism implemented. Currently, no locking mechanism is needed.
 */
final class PredisSessionHandler implements SessionHandlerInterface
{
    /**
     * @var ClientInterface
     */
    private ClientInterface $predis;

    /**
     * @var string
     */
    private string $keyPrefix;

    /**
     * @var int
     */
    private int $lifetime;

    /**
     * PredisSessionHandler constructor.
     *
     * @param ClientInterface $predis
     * @param string $keyPrefix
     * @param int $lifetime
     */
    public function __construct(ClientInterface $predis, string $keyPrefix, int $lifetime)
    {
        $this->predis = $predis;
        $this->keyPrefix = $keyPrefix;
        $this->lifetime = $lifetime;
    }

    /**
     * @inheritdoc
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function destroy($sessionId): bool
    {
        $this->predis->del(
            $this->generateKey($sessionId)
        );

        return true;
    }

    /**
     * @inheritdoc
     */
    public function gc($maxlifetime): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function open($savePath, $name): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function read($sessionId): string
    {
        return (string)$this->predis->get(
            $this->generateKey($sessionId)
        );
    }

    /**
     * @inheritdoc
     */
    public function write($sessionId, $sessionData): bool
    {
        $this->predis->setex(
            $this->generateKey($sessionId),
            $this->lifetime,
            $sessionData
        );

        return true;
    }

    /**
     * Combine prefix with current key.
     *
     * @param string $key
     *
     * @return string
     */
    private function generateKey(string $key): string
    {
        return $this->keyPrefix . $key;
    }
}
