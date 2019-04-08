<?php
declare(strict_types=1);

namespace {

    use Gaming\AppKernel;
    use Symfony\Component\Debug\Debug;
    use Symfony\Component\Dotenv\Dotenv;
    use Symfony\Component\HttpFoundation\Request;

    require_once __DIR__ . '/../vendor/autoload.php';

    $additionalEnvironmentPaths = [];
    $additionalEnvironmentPath = __DIR__ . '/../config/environment.env';

    if (file_exists($additionalEnvironmentPath)) {
        $additionalEnvironmentPaths[] = $additionalEnvironmentPath;
    }

    (new Dotenv())->load(__DIR__ . '/../config/environment.env.dist', ...$additionalEnvironmentPaths);

    $environment = getenv('APPLICATION_ENVIRONMENT');
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
