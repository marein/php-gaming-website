<?php
declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\EventStore;

use Exception;
use Gaming\Common\EventStore\EventStorePointer;
use Gaming\Common\EventStore\Exception\FailedRetrieveMostRecentPublishedStoredEventIdException;
use Gaming\Common\EventStore\Exception\FailedTrackMostRecentPublishedStoredEventIdException;
use Predis\Client;

final class PredisEventStorePointer implements EventStorePointer
{
    /**
     * @var Client
     */
    private $predis;

    /**
     * @var string
     */
    private $key;

    /**
     * PredisEventStorePointer constructor.
     *
     * @param Client $predis The predis instance which handles the connection.
     * @param string $key    The key where the id is stored
     */
    public function __construct(Client $predis, string $key)
    {
        $this->predis = $predis;
        $this->key = $key;
    }

    /**
     * @inheritdoc
     */
    public function trackMostRecentPublishedStoredEventId(int $id): void
    {
        try {
            $this->predis->set($this->key, (string)$id);
        } catch (Exception $e) {
            throw new FailedTrackMostRecentPublishedStoredEventIdException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function retrieveMostRecentPublishedStoredEventId(): int
    {
        try {
            if ($this->predis->exists($this->key)) {
                return (int)$this->predis->get($this->key);
            }

            return 0;
        } catch (Exception $e) {
            throw new FailedRetrieveMostRecentPublishedStoredEventIdException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
