<?php

declare(strict_types=1);

namespace Gaming\Common\SymfonyPrometheus;

use Prometheus\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

final class CollectMetricsListener
{
    public function __construct(
        private readonly RegistryInterface $registry,
        private readonly string $metricsNamespace,
        private readonly string $requestTimeAttribute = 'metrics_start_time'
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $event->getRequest()->attributes->set($this->requestTimeAttribute, microtime(true));
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $routeOrController = $this->getRouteOrController($event->getRequest());

        $this->registry->getOrRegisterCounter(
            $this->metricsNamespace,
            'http_requests_total',
            'Total number of HTTP requests.',
            ['method', 'route', 'code']
        )->inc(
            [
                $event->getRequest()->getMethod(),
                $routeOrController,
                (string)$event->getResponse()->getStatusCode()
            ]
        );

        $requestTime = $event->getRequest()->attributes->get($this->requestTimeAttribute);
        if ($requestTime === null) {
            return;
        }

        $this->registry->getOrRegisterHistogram(
            $this->metricsNamespace,
            'http_request_duration_seconds',
            'HTTP request latencies in seconds.',
            ['method', 'route'],
            [0.01, 0.02, 0.03, 0.04, 0.05, 0.075, 0.1, 0.25, 0.5, 2]
        )->observe(
            microtime(true) - $requestTime,
            [
                $event->getRequest()->getMethod(),
                $routeOrController
            ]
        );
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $this->registry->getOrRegisterCounter(
            $this->metricsNamespace,
            'http_exceptions_total',
            'The HTTP status codes returned.',
            ['method', 'route', 'class']
        )->inc(
            [
                $event->getRequest()->getMethod(),
                $this->getRouteOrController($event->getRequest()),
                $event->getThrowable()::class
            ]
        );
    }

    private function getRouteOrController(Request $request): string
    {
        return $request->attributes->get(
            '_route',
            $request->attributes->get('_controller', '')
        );
    }
}
