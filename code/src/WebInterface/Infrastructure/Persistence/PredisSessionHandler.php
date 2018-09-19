<?php
declare(strict_types=1);

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

    /**
     * @inheritdoc
     */
    public function close()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function destroy($sessionId)
    {
        $this->predis->del([
            $this->generateKey($sessionId)
        ]);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function gc($maxlifetime)
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function open($savePath, $name)
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function read($sessionId)
    {
        return (string)$this->predis->get(
            $this->generateKey($sessionId)
        );
    }

    /**
     * @inheritdoc
     */
    public function write($sessionId, $sessionData)
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
