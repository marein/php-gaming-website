<?php

declare(strict_types=1);

namespace Gaming\Common\Bus\Integration;

use Gaming\Common\Bus\HandlerDiscovery;
use Gaming\Common\Bus\PsrCompiledRoutingBus;
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
                ->set($this->extensionAlias . '.' . $name, PsrCompiledRoutingBus::class)
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

        foreach ((new HandlerDiscovery())->forClass($handlerClass) as $type => $method) {
            if (!preg_match($tag['match'] ?? '/.*/', $type)) {
                continue;
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
        }
    }
}
