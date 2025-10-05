<?php

declare(strict_types=1);

namespace Gaming\Common\SymfonyPrometheus;

use Prometheus\RegistryInterface;
use Prometheus\RenderTextFormat;
use Symfony\Component\HttpFoundation\Response;

final class MetricsController
{
    public function __construct(
        private readonly RegistryInterface $registry,
        private readonly string $metricsNamespace
    ) {
    }

    public function showAction(): Response
    {
        $this->registry->getOrRegisterGauge(
            $this->metricsNamespace,
            'apcu_memory_available',
            'The current size of available memory in bytes.'
        )->set(apcu_sma_info()['avail_mem']);

        return new Response(
            new RenderTextFormat()->render($this->registry->getMetricFamilySamples()),
            200,
            ['Content-Type' => RenderTextFormat::MIME_TYPE]
        );
    }
}
