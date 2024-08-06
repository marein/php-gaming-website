<?php

declare(strict_types=1);

namespace Gaming\Common\Bus\Integration;

use Closure;
use Gaming\Common\Bus\Request;
use Gaming\Common\Bus\RouteToMethodBus;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service_locator;

final class GamingPlatformBusBundle extends AbstractBundle implements CompilerPassInterface
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass($this);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('buses')
                ->defaultValue(['command' => null, 'query' => null])
                ->requiresAtLeastOneElement()
                ->useAttributeAsKey('name')
                ->arrayPrototype()
                    ->children()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param array{buses: array<string, array{name?: string}>} $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        foreach ($config['buses'] as $name => $busConfig) {
            $container->services()
                ->set($this->extensionAlias . '.' . $name, RouteToMethodBus::class)
                ->args([service_locator([]), []]);
        }
    }

    public function process(ContainerBuilder $container): void
    {
        foreach ($container->findTaggedServiceIds($this->extensionAlias . '.' . 'handler') as $handlerId => $tags) {
            foreach ($tags as $tag) {
                $this->processHandlerTag($container, $handlerId, $tag);
            }
        }
    }

    /**
     * @param array{bus?: string, match?: string} $tag
     */
    private function processHandlerTag(ContainerBuilder $container, string $handlerId, array $tag): void
    {
        $handlerClass = $container->getDefinition($handlerId)->getClass() ?? throw new InvalidArgumentException(
            'No class defined for service "' . $handlerId . '".'
        );
        $bus = $tag['bus'] ?? throw new InvalidArgumentException(
            'Option "bus" should be set in tag "' . $this->extensionAlias . '.' . 'handler' . '".'
        );
        $busDefinition = $container->getDefinition($this->extensionAlias . '.' . $bus);

        $addRoute = static function (string $type, string $method) use ($handlerId, $tag, $busDefinition): void {
            if (!preg_match($tag['match'] ?? '/.*/', $type)) {
                return;
            }

            if (array_key_exists($type, $busDefinition->getArgument(1))) {
                throw new LogicException(
                    sprintf(
                        'Cannot route type "%s" to "@%s->%s()", because it already routes to "@%s->%s()".',
                        $type,
                        $handlerId,
                        $method,
                        $busDefinition->getArgument(1)[$type]['handlerId'],
                        $busDefinition->getArgument(1)[$type]['method']
                    )
                );
            }

            $busDefinition->setArguments([
                service_locator($busDefinition->getArgument(0)->getValues() + [$handlerId => service($handlerId)]),
                $busDefinition->getArgument(1) + [$type => ['handlerId' => $handlerId, 'method' => $method]]
            ]);
        };

        $this->processClass($handlerClass, $addRoute);
    }

    /**
     * @param class-string $className
     * @param Closure(string, string): void $addRoute
     */
    private function processClass(string $className, Closure $addRoute): void
    {
        foreach ((new ReflectionClass($className))->getMethods() as $method) {
            $this->processMethod($method, $addRoute);
        }
    }

    /**
     * @param Closure(string, string): void $addRoute
     */
    private function processMethod(ReflectionMethod $method, Closure $addRoute): void
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
                $addRoute($type, $method->getName());
            }
        }
    }
}
