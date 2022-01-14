<?php

declare(strict_types=1);

namespace Gaming\Common\Normalizer;

use Gaming\Common\Normalizer\Exception\NormalizerException;

/**
 * This interface abstracts the library used for normalization so that
 * we can exchange it in the future.
 */
interface Normalizer
{
    /**
     * @return mixed A scalar or a nestable array of scalars.
     * @throws NormalizerException
     */
    public function normalize(mixed $value, string $typeName): mixed;

    /**
     * @param mixed $value A scalar or a nestable array of scalars.
     *
     * @throws NormalizerException
     */
    public function denormalize(mixed $value, string $typeName): mixed;
}
