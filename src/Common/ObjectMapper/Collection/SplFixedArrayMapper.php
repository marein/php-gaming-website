<?php
declare(strict_types=1);

namespace Gaming\Common\ObjectMapper\Collection;

use Gaming\Common\ObjectMapper\Mapper;
use SplFixedArray;

final class SplFixedArrayMapper implements Mapper
{
    /**
     * @var ArrayMapper
     */
    private $mapper;

    /**
     * SplFixedArrayMapper constructor.
     *
     * @param Mapper $mapper The Mapper which handles the serialization and deserialization for each element.
     */
    public function __construct(Mapper $mapper)
    {
        $this->mapper = new ArrayMapper($mapper);
    }

    /**
     * Serialize the given \SplFixedArray instance.
     *
     * @param SplFixedArray $value The \SplFixedArray to serialize.
     *
     * @return array
     */
    public function serialize($value)
    {
        return $this->mapper->serialize($value->toArray());
    }

    /**
     * Deserialize the given array to an \SplFixedArray instance.
     *
     * @param array $value The array to deserialize.
     *
     * @return SplFixedArray
     */
    public function deserialize($value)
    {
        return SplFixedArray::fromArray($this->mapper->deserialize($value));
    }
}
