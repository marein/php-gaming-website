<?php
declare(strict_types=1);

namespace {

    use Symfony\Component\Finder\Finder;

    $projectDirectory = dirname(__DIR__);

    require_once $projectDirectory . '/vendor/autoload.php';

    require_once $projectDirectory . '/var/cache/prod/srcGaming_KernelProdContainer.preload.php';

    $finder = Finder::create()
        ->files()
        ->in($projectDirectory . '/src')
        ->name('*.php');

    foreach ($finder as $file) {
        require_once $file->getRealPath();
    }
}
