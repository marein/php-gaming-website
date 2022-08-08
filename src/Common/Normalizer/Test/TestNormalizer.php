<?php

declare(strict_types=1);

namespace Gaming\Common\Normalizer\Test;

use Gaming\Common\Normalizer\Normalizer;

/**
 * This class can be used for testing purposes.
 */
final class TestNormalizer implements Normalizer
{
    public function normalize(mixed $value, string $typeName): mixed
    {
        return $value;
    }

    public function denormalize(mixed $value, string $typeName): mixed
    {
        return $value;
    }
}
