<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Console;

use Gaming\Common\Bus\Bus;
use Gaming\Common\ForkPool\Integration\Prometheus\ExposeMetricsTask;
use Gaming\Common\Timer\Integration\HandleTimeoutsCommandTemplate;
use Gaming\Common\Timer\TimeoutService;
use Gaming\ConnectFour\Application\Game\Command\TimeoutCommand;
use Prometheus\RegistryInterface;

final class HandleTimeoutsCommand extends HandleTimeoutsCommandTemplate
{
    public function __construct(
        private readonly Bus $commandBus,
        TimeoutService $timeoutService,
        RegistryInterface $prometheusRegistry,
        ExposeMetricsTask $exposeMetricsTask
    ) {
        parent::__construct(
            $timeoutService,
            $prometheusRegistry,
            'connect_four',
            $exposeMetricsTask
        );
    }

    protected function handleTimeout(string $timeoutId): void
    {
        $this->commandBus->handle(new TimeoutCommand($timeoutId));
    }
}
