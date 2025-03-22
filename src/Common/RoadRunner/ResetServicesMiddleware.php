<?php

declare(strict_types=1);

namespace Gaming\Common\RoadRunner;

use Baldinof\RoadRunnerBundle\Http\MiddlewareInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Contracts\Service\ResetInterface;

final class ResetServicesMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ResetInterface $resettable
    ) {
    }

    public function process(Request $request, HttpKernelInterface $next): \Iterator
    {
        yield $next->handle($request);

        $this->resettable->reset();
    }
}
