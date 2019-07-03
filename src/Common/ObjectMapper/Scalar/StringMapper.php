<?php
declare(strict_types=1);

namespace Gaming\Common\ObjectMapper\Scalar;

use Gaming\Common\ObjectMapper\Mapper;

final class StringMapper implements Mapper
{
    /**
     * @inheritdoc
     * @return string
     */
    public function serialize($value)
    {
        return (string)$value;
    }

    /**
     * @inheritdoc
     * @return string
     */
    public function deserialize($value)
    {
        return (string)$value;
    }
}
