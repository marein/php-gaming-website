<?php

declare(strict_types=1);

namespace Gaming\Common\Bus;

use Generator;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;

final class HandlerDiscovery
{
    /**
     * @param class-string $className
     *
     * @return Generator<string, string>
     */
    public function forClass(string $className): Generator
    {
        foreach ((new ReflectionClass($className))->getMethods() as $method) {
            yield from $this->forMethod($method);
        }
    }

    /**
     * @return Generator<string, string>
     */
    private function forMethod(ReflectionMethod $method): Generator
    {
        if (
            !$method->isPublic() ||
            $method->isStatic() ||
            preg_match('/^__(?!invoke$)/', $method->getName()) ||
            $method->getNumberOfParameters() !== 1
        ) {
            return;
        }

        $parameterType = $method->getParameters()[0]->getType();
        $parameterTypes = match (true) {
            $parameterType instanceof ReflectionNamedType => [$parameterType->getName()],
            $parameterType instanceof ReflectionUnionType => array_map(
                static fn(ReflectionNamedType $reflectionType): string => $reflectionType->getName(),
                array_filter(
                    $parameterType->getTypes(),
                    static fn($reflectionType): bool => $reflectionType instanceof ReflectionNamedType
                )
            ),
            default => []
        };

        foreach ($parameterTypes as $type) {
            if (class_exists($type) && in_array(Request::class, class_implements($type))) {
                yield $type => $method->getName();
            }
        }
    }
}
