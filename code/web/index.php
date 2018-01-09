<?php

namespace {

    use Gambling\AppKernel;
    use Marein\FriendVisibility\FriendConfiguration;
    use Symfony\Component\Debug\Debug;
    use Symfony\Component\HttpFoundation\Request;

    require_once __DIR__ . '/../vendor/autoload.php';

    $environment = getenv('ENVIRONMENT');
    $isDevelopmentEnvironment = $environment !== 'prod';

    if ($isDevelopmentEnvironment) {
        Debug::enable();
    }

    $kernel = new AppKernel($environment, $isDevelopmentEnvironment);
    $request = Request::createFromGlobals();
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);
}
