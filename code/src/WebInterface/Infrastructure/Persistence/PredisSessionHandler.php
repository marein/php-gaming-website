<?php

namespace Gambling\WebInterface\Infrastructure\Persistence;

use Predis\Client;

/**
 * Class PredisSessionHandler
 *
 * There is no locking mechanism implemented. Currently, no locking mechanism is needed.
 */
final class PredisSessionHandler implements \SessionHandlerInterface
{
    /**
     * @var Client
     */
    private $predis;

    /**
     * @var string
     */
    private $keyPrefix;

    /**
     * @var int
     */
    private $lifetime;

    /**
     * PredisSessionHandler constructor.
     *
     * @param Client $predis
     * @param string $keyPrefix
     * @param int    $lifetime
     */
    public function __construct(Client $predis, string $keyPrefix, int $lifetime)
    {
        $this->predis = $predis;
        $this->keyPrefix = $keyPrefix;
        $this->lifetime = $lifetime;
    }

    public function close()
    {
        return true;
    }

    public function destroy($sessionId)
    {
        $result = $this->predis->del([
            $this->generateKey($sessionId)
        ]);

        return (bool)$result;
    }

    public function gc($maxlifetime)
    {
        return true;
    }

    public function open($savePath, $name)
    {
        return true;
    }

    public function read($sessionId)
    {
        return (string)$this->predis->get(
            $this->generateKey($sessionId)
        );
    }

    public function write($sessionId, $sessionData)
    {
        return $this->predis->setex(
            $this->generateKey($sessionId),
            $this->lifetime,
            $sessionData
        );
    }

    private function generateKey(string $key)
    {
        return $this->keyPrefix . $key;
    }
}
