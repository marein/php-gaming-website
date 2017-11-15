<?php

namespace Gambling\Common\ObjectMapper\Scalar;

use Gambling\Common\ObjectMapper\Mapper;

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
