<?php
declare(strict_types=1);

namespace Gaming\Common\ObjectMapper;

use Gaming\Common\ObjectMapper\Exception\MapperException;

/**
 * This is a very very generic definition of a mapper.
 */
interface Mapper
{
    /**
     * Serialize the given value. The return type can be type of int, string, float, bool, null or an array with
     * scalars.
     *
     * @param int|string|float|bool|null|array|object $value The value which gets serialized. This can be anything.
     *
     * @return int|string|float|bool|null|array
     * @throws MapperException
     */
    public function serialize($value);

    /**
     * Deserialize the given value. The return type can be anything.
     *
     * @param int|string|float|bool|null|array $value The value can be type of int, string, float, bool, null or an
     *                                                array with scalars.
     *
     * @return int|string|float|bool|null|array|object
     * @throws MapperException
     */
    public function deserialize($value);
}
