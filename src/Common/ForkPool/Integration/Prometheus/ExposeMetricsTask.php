<?php

declare(strict_types=1);

namespace Gaming\Common\ForkPool\Integration\Prometheus;

use Amp\Http\HttpStatus;
use Amp\Http\Server\DefaultErrorHandler;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\SocketHttpServer;
use Gaming\Common\ForkPool\Channel\Channel;
use Gaming\Common\ForkPool\Task;
use Prometheus\RegistryInterface;
use Prometheus\RenderTextFormat;
use Psr\Log\LoggerInterface;

use function Amp\trapSignal;

final class ExposeMetricsTask implements Task
{
    public function __construct(
        private readonly string $socketAddress,
        private readonly RegistryInterface $registry,
        private readonly string $metricsNamespace,
        private readonly LoggerInterface $logger
    ) {
    }

    public function execute(Channel $channel): int
    {
        $server = SocketHttpServer::createForDirectAccess($this->logger);
        $server->expose($this->socketAddress);
        $server->start(
            new class ($this->registry, $this->metricsNamespace) implements RequestHandler {
                public function __construct(
                    private readonly RegistryInterface $registry,
                    private readonly string $metricsNamespace
                ) {
                }

                public function handleRequest(Request $request): Response
                {
                    $this->registry->getOrRegisterGauge(
                        $this->metricsNamespace,
                        'apcu_memory_available',
                        'The current size of available memory in bytes.',
                    )->set(apcu_sma_info()['avail_mem']);

                    return new Response(
                        HttpStatus::OK,
                        ['Content-Type' => 'text/plain'],
                        new RenderTextFormat()->render($this->registry->getMetricFamilySamples()),
                    );
                }
            },
            new DefaultErrorHandler()
        );

        trapSignal([SIGINT, SIGTERM]);

        $server->stop();

        return 0;
    }
}
