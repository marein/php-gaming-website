<?php
declare(strict_types=1);

namespace Gaming\Common\ObjectMapper\Scalar;

use Gaming\Common\ObjectMapper\Mapper;

final class FloatMapper implements Mapper
{
    /**
     * @inheritdoc
     * @return float
     */
    public function serialize($value)
    {
        return (float)$value;
    }

    /**
     * @inheritdoc
     * @return float
     */
    public function deserialize($value)
    {
        return (float)$value;
    }
}
