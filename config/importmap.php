<?php

declare(strict_types=1);

namespace {

    use Symfony\Component\Finder\Finder;

    $projectDirectory = dirname(__DIR__);

    require_once $projectDirectory . '/vendor/autoload.php';

    $finder = Finder::create()
        ->files()
        ->in($projectDirectory . '/config/*')
        ->name('importmap.php');

    $map = [
        'app' => ['path' => 'js/app.js', 'preload' => true]
    ];

    foreach ($finder as $file) {
        $map = array_merge($map, require $file->getRealPath());
    }

    return $map;
}
