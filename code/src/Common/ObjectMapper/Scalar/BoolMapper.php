<?php
declare(strict_types=1);

namespace Gambling\Common\ObjectMapper\Scalar;

use Gambling\Common\ObjectMapper\Mapper;

final class BoolMapper implements Mapper
{
    /**
     * @inheritdoc
     * @return bool
     */
    public function serialize($value)
    {
        return (bool)$value;
    }

    /**
     * @inheritdoc
     * @return bool
     */
    public function deserialize($value)
    {
        return (bool)$value;
    }
}
