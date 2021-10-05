<?php
declare(strict_types=1);

namespace Gaming\Common\Normalizer;

use Gaming\Common\Normalizer\Exception\NormalizerException;

interface Normalizer
{
    /**
     * @throws NormalizerException
     */
    public function normalize(mixed $value, string $typeName): mixed;

    /**
     * @throws NormalizerException
     */
    public function denormalize(mixed $value, string $typeName): mixed;
}
