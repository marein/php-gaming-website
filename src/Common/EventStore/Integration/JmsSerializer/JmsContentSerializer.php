<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\Integration\JmsSerializer;

use Gaming\Common\EventStore\ContentSerializer;
use Gaming\Common\EventStore\Exception\EventStoreException;
use JMS\Serializer\ArrayTransformerInterface;
use Throwable;

final class JmsContentSerializer implements ContentSerializer
{
    /**
     * @param string $typeName Must be a polymorphic type, otherwise JMS cannot create a self-describing structure.
     */
    public function __construct(
        private readonly ArrayTransformerInterface $jms,
        private readonly string $typeName
    ) {
    }

    public function serialize(object $content): string
    {
        try {
            return json_encode(
                $this->jms->toArray($content, null, $this->typeName),
                JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR
            );
        } catch (Throwable $e) {
            throw new EventStoreException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function deserialize(string $content): object
    {
        try {
            return $this->jms->fromArray(json_decode($content, true, 512, JSON_THROW_ON_ERROR), $this->typeName);
        } catch (Throwable $e) {
            throw new EventStoreException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
