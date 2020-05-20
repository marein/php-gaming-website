<?php
declare(strict_types=1);

namespace {

    use Gaming\Kernel;
    use Symfony\Component\Dotenv\Dotenv;
    use Symfony\Component\ErrorHandler\Debug;
    use Symfony\Component\HttpFoundation\Request;

    require_once __DIR__ . '/../vendor/autoload.php';

    (new Dotenv(false))->loadEnv(__DIR__ . '/../config/environment.env', 'APPLICATION_ENVIRONMENT');

    $environment = $_SERVER['APPLICATION_ENVIRONMENT'] ?? $_ENV['APPLICATION_ENVIRONMENT'] ?? 'dev';
    $isDevelopmentEnvironment = $environment !== 'prod';

    if ($isDevelopmentEnvironment) {
        Debug::enable();
    }

    $kernel = new Kernel($environment, $isDevelopmentEnvironment);
    $request = Request::createFromGlobals();
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);
}
