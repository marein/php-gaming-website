<?php
declare(strict_types=1);

namespace Gaming\Common\ObjectMapper;

use ReflectionProperty;

/**
 * This class represents a property for the ObjectMapper.
 */
final class Property
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var Mapper
     */
    private Mapper $mapper;

    /**
     * @var string
     */
    private string $serializedName;

    /**
     * @var ReflectionProperty
     */
    private ReflectionProperty $reflectionProperty;

    /**
     * Property constructor.
     *
     * @param string              $name
     * @param string              $serializedName
     * @param ReflectionProperty $reflectionProperty
     * @param Mapper              $mapper
     */
    public function __construct(
        string $name,
        string $serializedName,
        ReflectionProperty $reflectionProperty,
        Mapper $mapper
    ) {
        $this->name = $name;
        $this->serializedName = $serializedName;
        $this->reflectionProperty = $reflectionProperty;
        $this->mapper = $mapper;
    }

    /**
     * Returns the name.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Returns the mapper.
     *
     * @return Mapper
     */
    public function mapper(): Mapper
    {
        return $this->mapper;
    }

    /**
     * Returns the serialized name.
     *
     * @return string
     */
    public function serializedName(): string
    {
        return $this->serializedName;
    }

    /**
     * Returns the reflection property.
     *
     * @return ReflectionProperty
     */
    public function reflectionProperty(): ReflectionProperty
    {
        return $this->reflectionProperty;
    }
}
