<?php
declare(strict_types=1);

namespace Gaming\Common\ObjectMapper;

/**
 * This class represents a discriminator for the DiscriminatorMapper.
 */
final class Discriminator
{
    /**
     * @var string
     */
    private string $className;

    /**
     * @var string
     */
    private string $serializedClassName;

    /**
     * @var Mapper
     */
    private Mapper $mapper;

    /**
     * Discriminator constructor.
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
