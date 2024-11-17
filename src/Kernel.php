<?php

declare(strict_types=1);

namespace Gaming;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use Gaming\Common\Bus\Integration\GamingPlatformBusBundle;
use Marein\LockDoctrineMigrationsBundle\MareinLockDoctrineMigrationsBundle;
use Marein\StandardHeadersCsrfBundle\MareinStandardHeadersCsrfBundle;
use Symfony\Bundle\DebugBundle\DebugBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Bundle\WebProfilerBundle\WebProfilerBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

final class Kernel extends BaseKernel
{
    /**
     * @return BundleInterface[]
     */
    public function registerBundles(): array
    {
        $bundles = [
            new FrameworkBundle(),
            new DoctrineBundle(),
            new DoctrineMigrationsBundle(),
            new MareinLockDoctrineMigrationsBundle(),
            new MareinStandardHeadersCsrfBundle(),
            new TwigBundle(),
            new MonologBundle(),
            new SecurityBundle(),
            new GamingPlatformBusBundle()
        ];

        if ($this->getEnvironment() === 'dev') {
            $bundles[] = new DebugBundle();
            $bundles[] = new WebProfilerBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load($this->getProjectDir() . '/config/config_' . $this->getEnvironment() . '.yml');
    }
}
