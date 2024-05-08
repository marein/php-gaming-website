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
        'uhtml/node.js' => ['version' => '4.5.8']
    ];

    foreach ($finder as $file) {
        $map = array_merge($map, require $file->getRealPath());
    }

    return $map;
}
