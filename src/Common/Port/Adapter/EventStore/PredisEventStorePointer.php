<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\EventStore;

use Exception;
use Gaming\Common\EventStore\EventStorePointer;
use Gaming\Common\EventStore\Exception\FailedRetrieveMostRecentPublishedStoredEventIdException;
use Gaming\Common\EventStore\Exception\FailedTrackMostRecentPublishedStoredEventIdException;
use Predis\ClientInterface;

final class PredisEventStorePointer implements EventStorePointer
{
    private ClientInterface $predis;

    private string $key;

    public function __construct(ClientInterface $predis, string $key)
    {
        $this->predis = $predis;
        $this->key = $key;
    }

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
