<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore;

use Gaming\Common\EventStore\Exception\EventStoreException;

interface ContentSerializer
{
    /**
     * @return string Must be self-describing so that it can be deserialized.
     * @throws EventStoreException
     */
    public function serialize(object $content): string;

    /**
     * @throws EventStoreException
     */
    public function deserialize(string $content): object;
}
