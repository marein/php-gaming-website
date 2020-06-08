<?php
declare(strict_types=1);

namespace Gaming\Common\CsrfProtectionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('csrf_protection');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('protected_paths')
                    ->scalarPrototype()->end()
                    ->defaultValue(['^/'])
                ->end()
                ->arrayNode('allowed_origins')
                    ->scalarPrototype()->end()
                ->end()
                ->booleanNode('fallback_to_referer')->defaultTrue()->end()
                ->booleanNode('allow_null_origin')->defaultFalse()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
