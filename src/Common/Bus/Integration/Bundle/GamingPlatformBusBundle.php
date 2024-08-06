<?php

declare(strict_types=1);

namespace Gaming\Common\Bus\Integration\Bundle;

use Closure;
use Gaming\Common\Bus\Request;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_locator;

final class GamingPlatformBusBundle extends AbstractBundle implements CompilerPassInterface
{
    /**
     * @var array{buses: array<int, array{serviceId: string, handlerTag: string}>}
     */
    private array $passConfig = ['buses' => []];

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass($this);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('buses')
                ->defaultValue(['default' => null])
                ->requiresAtLeastOneElement()
                ->useAttributeAsKey('name')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('serviceId')->defaultNull()->end()
                        ->scalarNode('handlerTag')->defaultNull()->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param array{buses: array<string, ?array{serviceId?: string, handlerTag?: string}>} $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        foreach ($config['buses'] as $name => $busConfig) {
            $serviceId = $busConfig['serviceId'] ?? $this->extensionAlias . '.' . $name;
            $handlerTag = $busConfig['handlerTag'] ?? $serviceId;

            $container->services()
                ->set($serviceId, RoutingBus::class)
                ->arg(0, tagged_locator($handlerTag))
                ->arg(1, abstract_arg('routes'));

            $this->passConfig['buses'][] = ['serviceId' => $serviceId, 'handlerTag' => $handlerTag];
        }
    }

    public function process(ContainerBuilder $container): void
    {
        foreach ($this->passConfig['buses'] as $bus) {
            $this->processBus($container, $bus);
        }
    }

    /**
     * @param array{serviceId: string, handlerTag: string} $bus
     */
    private function processBus(ContainerBuilder $container, array $bus): void
    {
        $routes = [];

        foreach ($container->findTaggedServiceIds($bus['handlerTag']) as $handlerId => $tag) {
            $handlerClass = $container->getDefinition($handlerId)->getClass() ?? throw new InvalidArgumentException(
                'No class defined for service "' . $handlerId . '".'
            );

            $addRoute = static function (string $type, string $method) use ($handlerId, &$routes): void {
                array_key_exists($type, $routes) && throw new LogicException(
                    sprintf(
                        'Cannot route type "%s" to "@%s->%s()", because it already routes to "@%s->%s()".',
                        $type,
                        $handlerId,
                        $method,
                        $routes[$type]['handlerId'],
                        $routes[$type]['method']
                    )
                );

                $routes[$type] = ['handlerId' => $handlerId, 'method' => $method];
            };

            $this->processClass($handlerClass, $addRoute);
        }

        $container->getDefinition($bus['serviceId'])->setArgument(1, $routes);
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
    public function processMethod(ReflectionMethod $method, Closure $addRoute): void
    {
        if (
            !$method->isPublic() ||
            $method->isStatic() ||
            preg_match('/^__(?!invoke$)/', $method->getName()) ||
            $method->getNumberOfParameters() !== 1
        ) {
            return;
        }

        $this->processReflectionType(
            $method->getParameters()[0]->getType(),
            static fn(string $type) => $addRoute($type, $method->getName())
        );
    }

    /**
     * @param Closure(string): void $addRoute
     */
    private function processReflectionType(?ReflectionType $reflectionType, Closure $addRoute): void
    {
        $types = match (true) {
            $reflectionType instanceof ReflectionNamedType => [$reflectionType->getName()],
            $reflectionType instanceof ReflectionUnionType => array_map(
                static fn(ReflectionNamedType $reflectionType): string => $reflectionType->getName(),
                array_filter(
                    $reflectionType->getTypes(),
                    static fn($reflectionType): bool => $reflectionType instanceof ReflectionNamedType
                )
            ),
            default => []
        };

        foreach ($types as $type) {
            if (!class_exists($type) || !in_array(Request::class, class_implements($type))) {
                continue;
            }

            $addRoute($type);
        }
    }
}
