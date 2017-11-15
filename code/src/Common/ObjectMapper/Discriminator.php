<?php

namespace Gambling\Common\ObjectMapper;

/**
 * This class represents a discriminator for the DiscriminatorMapper.
 */
final class Discriminator
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $serializedClassName;

    /**
     * @var Mapper
     */
    private $mapper;

    /**
     * Property constructor.
     *
     * @param string $className
     * @param Mapper $mapper
     * @param string $serializedClassName
     */
    public function __construct(string $className, string $serializedClassName, Mapper $mapper)
    {
        $this->className = $className;
        $this->serializedClassName = $serializedClassName;
        $this->mapper = $mapper;
    }

    /**
     * Returns the class name.
     *
     * @return string
     */
    public function className(): string
    {
        return $this->className;
    }

    /**
     * Returns the serialized class name.
     *
     * @return string
     */
    public function serializedClassName(): string
    {
        return $this->serializedClassName;
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
}
