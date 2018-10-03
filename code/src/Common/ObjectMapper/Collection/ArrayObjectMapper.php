<?php
declare(strict_types=1);

namespace Gaming\Common\ObjectMapper\Collection;

use Gaming\Common\ObjectMapper\Mapper;

final class ArrayObjectMapper implements Mapper
{
    /**
     * @var ArrayMapper
     */
    private $mapper;

    /**
     * ArrayObjectMapper constructor.
     *
     * @param Mapper $mapper The Mapper which handles the serialization and deserialization for each element.
     */
    public function __construct(Mapper $mapper)
    {
        $this->mapper = new ArrayMapper($mapper);
    }

    /**
     * Serialize the given \ArrayObject instance.
     *
     * @param \ArrayObject $value The \ArrayObject to serialize.
     *
     * @return array
     */
    public function serialize($value)
    {
        return $this->mapper->serialize($value->getArrayCopy());
    }

    /**
     * Deserialize the given array to an \ArrayObject instance.
     *
     * @param array $value The array to deserialize.
     *
     * @return \ArrayObject
     */
    public function deserialize($value)
    {
        return new \ArrayObject($this->mapper->deserialize($value));
    }
}
