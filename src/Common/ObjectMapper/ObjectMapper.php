<?php
declare(strict_types=1);

namespace Gaming\Common\ObjectMapper;

use Gaming\Common\ObjectMapper\Exception\MapperException;
use ReflectionClass;
use ReflectionProperty;

final class ObjectMapper implements Mapper
{
    /**
     * @var string
     */
    private string $className;

    /**
     * @var ReflectionClass
     */
    private ReflectionClass $reflectionClass;

    /**
     * @var Property[]
     */
    private array $properties;

    /**
     * ObjectMapper constructor.
     *
     * @param string $className The class name handled by this mapper.
     */
    public function __construct(string $className)
    {
        $this->className = $className;
        $this->reflectionClass = new ReflectionClass($this->className);
        $this->properties = [];
    }

    /**
     * Add a property which gets serialized and deserialized.
     *
     * @param string      $name           The name of the property in the class.
     * @param Mapper      $mapper         The Mapper which handles the serialization and deserialization.
     * @param string|null $serializedName The optional serialized name. Default is the name.
     *
     * @throws MapperException
     */
    public function addProperty(string $name, Mapper $mapper, string $serializedName = null): void
    {
        $class = $this->reflectionClass;

        // The property is maybe located in a parent class. Look for it.
        while ($class !== false && !$class->hasProperty($name)) {
            $class = $class->getParentClass();
        }

        if ($class === false) {
            throw new MapperException('Property "' . $name . '" in class "' . $this->className . '" does not exists.');
        }

        $reflectionProperty = new ReflectionProperty($class->getName(), $name);
        $reflectionProperty->setAccessible(true);

        $this->properties[] = new Property($name, $serializedName ?? $name, $reflectionProperty, $mapper);
    }

    /**
     * Serialize the given object to an array.
     *
     * @param object|null $value The object to serialize.
     *
     * @return array|null
     * @throws MapperException
     */
    public function serialize($value)
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof $this->className) {
            $serialized = [];

            foreach ($this->properties as $property) {
                $serialized[$property->serializedName()] = $property->mapper()->serialize(
                    $property->reflectionProperty()->getValue($value)
                );
            }

            return $serialized;
        }

        throw new MapperException(sprintf(
            '%s::serialize expects first parameter to be %s or null. Type of %s given.',
            self::class,
            $this->className,
            is_object($value) ? get_class($value) : gettype($value)
        ));
    }

    /**
     * Deserialize the given array to an object.
     *
     * @param array|null $value The array to deserialize.
     *
     * @return object|null
     * @throws MapperException
     */
    public function deserialize($value)
    {
        if ($value === null) {
            return null;
        }

        if (!is_array($value)) {
            throw new MapperException(sprintf(
                '%s::deserialize expects first parameter to be array or null. Type of %s given.',
                self::class,
                gettype($value)
            ));
        }

        $object = $this->reflectionClass->newInstanceWithoutConstructor();

        foreach ($this->properties as $property) {
            if (array_key_exists($property->serializedName(), $value)) {
                $property->reflectionProperty()->setValue(
                    $object,
                    $property->mapper()->deserialize($value[$property->serializedName()])
                );
            } else {
                throw new MapperException('Key "' . $property->serializedName() . '" does not exists.');
            }
        }

        return $object;
    }
}
