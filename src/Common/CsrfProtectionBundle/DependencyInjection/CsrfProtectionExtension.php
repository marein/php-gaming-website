<?php
declare(strict_types=1);

namespace Gaming\Common\CsrfProtectionBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class CsrfProtectionExtension extends Extension
{
    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(dirname(__DIR__) . '/Resources/config')
        );
        $loader->load('services.xml');

        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->getDefinition('csrf_protection.guard.path_guard')
            ->replaceArgument(0, $config['protected_paths']);

        $container->getDefinition('csrf_protection.guard.origin_header_guard')
            ->replaceArgument(0, $config['allowed_origins']);

        $container->getDefinition('csrf_protection.guard.referer_header_guard')
            ->replaceArgument(0, $config['allowed_origins']);

        $container->getDefinition('csrf_protection.guard.referer_header_guard.feature_toggle')
            ->replaceArgument(0, $config['fallback_to_referer']);

        $container->getDefinition('csrf_protection.guard.null_origin_header_guard.feature_toggle')
            ->replaceArgument(0, $config['allow_null_origin']);
    }
}
