<?php
declare(strict_types=1);

namespace Gaming\Common\ObjectMapper\Collection;

use Gaming\Common\ObjectMapper\Mapper;

final class ArrayMapper implements Mapper
{
    /**
     * @var Mapper
     */
    private Mapper $mapper;

    /**
     * ArrayMapper constructor.
     *
     * @param Mapper $mapper The Mapper which handles the serialization and deserialization for each element.
     */
    public function __construct(Mapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * Serialize the given array.
     *
     * @param array $value The array to serialize.
     *
     * @return array
     */
    public function serialize($value)
    {
        return array_map(
            function ($v) {
                return $this->mapper->serialize($v);
            },
            $value
        );
    }

    /**
     * Deserialize the given array.
     *
     * @param array $value The array to deserialize.
     *
     * @return array
     */
    public function deserialize($value)
    {
        return array_map(
            function ($v) {
                return $this->mapper->deserialize($v);
            },
            $value
        );
    }
}
