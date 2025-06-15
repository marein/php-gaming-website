<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Console;

use Gaming\Common\Bus\Bus;
use Gaming\Common\Timer\TimeoutService;
use Gaming\ConnectFour\Application\Game\Command\TimeoutCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class HandleTimeoutsCommand extends Command
{
    public function __construct(
        private readonly Bus $commandBus,
        private readonly TimeoutService $timeoutService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->timeoutService->listen(
            fn(string $timeoutId) => $this->commandBus->handle(new TimeoutCommand($timeoutId))
        );

        return Command::SUCCESS;
    }
}
