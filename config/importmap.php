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
        'notification-list' => ['path' => 'js/Common/NotificationList.js'],
        'event-source-status' => ['path' => 'js/Common/EventSourceStatus.js'],
        'confirmation-button' => ['path' => 'js/Common/ConfirmationButton.js'],
        'uhtml/node.js' => ['version' => '4.7.0'],
        '@tabler/core/dist/css/tabler.min.css' => ['version' => '1.1.1', 'type' => 'css']
    ];

    foreach ($finder as $file) {
        $map = array_merge($map, require $file->getRealPath());
    }

    return $map;
}
