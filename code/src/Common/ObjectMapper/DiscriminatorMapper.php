<?php

namespace Gambling\Common\ObjectMapper;

use Gambling\Common\ObjectMapper\Exception\MapperException;

final class DiscriminatorMapper implements Mapper
{
    /**
     * @var string
     */
    private $discriminatorName;

    /**
     * @var Discriminator[]
     */
    private $discriminatorsByClassName;

    /**
     * @var Discriminator[]
     */
    private $discriminatorsBySerializedClassName;

    /**
     * DiscriminatorMapper constructor.
     *
     * @param string $discriminatorName The discriminator name becomes the key with the type definition.
     */
    public function __construct(string $discriminatorName)
    {
        $this->discriminatorName = $discriminatorName;
        $this->discriminatorsByClassName = [];
        $this->discriminatorsBySerializedClassName = [];
    }

    /**
     * Add a discriminator which gets serialized and deserialized.
     *
     * @param string      $className           The name of the class handled by the discriminator.
     * @param Mapper      $mapper              The Mapper which handles the serialization and deserialization.
     * @param string|null $serializedClassName The optional serialized class name. Default is the class name.
     */
    public function addDiscriminator(string $className, Mapper $mapper, string $serializedClassName = null): void
    {
        $discriminator = new Discriminator($className, $serializedClassName ?? $className, $mapper);

        // Cache the discriminator in different arrays for quick access.
        $this->discriminatorsByClassName[$discriminator->className()] = $discriminator;
        $this->discriminatorsBySerializedClassName[$discriminator->serializedClassName()] = $discriminator;
    }

    /**
     * Serialize the given object to an array.
     *
     * @param mixed $value The object to serialize.
     *
     * @return array|null
     * @throws MapperException
     */
    public function serialize($value)
    {
        $className = get_class($value);

        if (!isset($this->discriminatorsByClassName[$className])) {
            throw new MapperException('Class "' . $className . '" not found.');
        }

        $discriminator = $this->discriminatorsByClassName[$className];

        $serialized = $discriminator->mapper()->serialize($value);
        $serialized[$this->discriminatorName] = $discriminator->serializedClassName();

        return $serialized;
    }

    /**
     * Deserialize the given array to an object.
     *
     * @param mixed $value The array to deserialize.
     *
     * @return null|object
     * @throws MapperException
     */
    public function deserialize($value)
    {
        if (!isset($value[$this->discriminatorName])) {
            throw new MapperException('Discriminator "' . $this->discriminatorName . '" not found.');
        }

        $className = $value[$this->discriminatorName];

        if (!isset($this->discriminatorsBySerializedClassName[$className])) {
            throw new MapperException('Class "' . $className . '" not found.');
        }

        $discriminator = $this->discriminatorsBySerializedClassName[$className];

        return $discriminator->mapper()->deserialize($value);
    }
}
