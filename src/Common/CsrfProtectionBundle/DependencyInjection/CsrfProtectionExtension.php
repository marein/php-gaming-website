<?php
declare(strict_types=1);

namespace Gaming\Common\CsrfProtectionBundle\DependencyInjection;

use Gaming\Common\CsrfProtectionBundle\EventListener\CsrfProtectionListener;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\HttpKernel\KernelEvents;

final class CsrfProtectionExtension extends Extension
{
    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setDefinition(
            'csrf_protection.event_listener.csrf_protection_listener',
            (new Definition(CsrfProtectionListener::class))
                ->setArgument(0, $config['protected_paths'])
                ->setArgument(1, $config['allowed_origins'])
                ->setArgument(2, $config['fallback_to_referer'])
                ->setArgument(3, $config['allow_null_origin'])
                ->addTag('kernel.event_listener', ['event' => KernelEvents::REQUEST])
        );
    }
}
