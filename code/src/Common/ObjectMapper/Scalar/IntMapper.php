<?php
declare(strict_types=1);

namespace Gaming\Common\ObjectMapper\Scalar;

use Gaming\Common\ObjectMapper\Mapper;

final class IntMapper implements Mapper
{
    /**
     * @inheritdoc
     * @return int
     */
    public function serialize($value)
    {
        return (int)$value;
    }

    /**
     * @inheritdoc
     * @return int
     */
    public function deserialize($value)
    {
        return (int)$value;
    }
}
