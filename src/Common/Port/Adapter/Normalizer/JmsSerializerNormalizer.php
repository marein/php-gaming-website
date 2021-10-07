<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Normalizer;

use Gaming\Common\Normalizer\Exception\NormalizerException;
use Gaming\Common\Normalizer\Normalizer;
use JMS\Serializer\ArrayTransformerInterface;
use Throwable;

final class JmsSerializerNormalizer implements Normalizer
{
    public function __construct(
        private ArrayTransformerInterface $jms
    ) {
    }

    public function normalize(mixed $value, string $typeName): mixed
    {
        try {
            return $this->jms->toArray($value, null, $typeName);
        } catch (Throwable $e) {
            throw new NormalizerException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    public function denormalize(mixed $value, string $typeName): mixed
    {
        try {
            return $this->jms->fromArray($value, $typeName);
        } catch (Throwable $e) {
            throw new NormalizerException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}
