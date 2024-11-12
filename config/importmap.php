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
        'app' => ['path' => 'js/app.js', 'entrypoint' => true],
        'event-source' => ['path' => 'js/Common/EventSource.js'],
        'notification-list' => ['path' => 'js/Common/NotificationList.js'],
        'event-source-status' => ['path' => 'js/Common/EventSourceStatus.js'],
        'uhtml/node.js' => ['version' => '4.5.8'],
        '@tabler/core/dist/css/tabler.min.css' => ['version' => '1.0.0-beta20', 'type' => 'css'],
        '@tabler/core/dist/js/tabler.min.js' => ['version' => '1.0.0-beta20']
    ];

    foreach ($finder as $file) {
        $map = array_merge($map, require $file->getRealPath());
    }

    return $map;
}
