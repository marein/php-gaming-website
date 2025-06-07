<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Console;

use Gaming\Common\Bus\Bus;
use Gaming\ConnectFour\Application\Game\Command\TimeoutCommand;
use Gaming\ConnectFour\Port\Adapter\Persistence\Repository\PredisTimerStore;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class HandleTimeoutsCommand extends Command
{
    private const int MAX_SLEEP = 250000;
    private const int LOOKAHEAD_WINDOW = 3000;

    public function __construct(
        private readonly Bus $commandBus,
        private readonly PredisTimerStore $predisTimerStore
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        while (true) {
            $nowMs = (int)(microtime(true) * 1000);
            $estimatedSleepTime = self::MAX_SLEEP;

            $gameIds = $this->predisTimerStore->findGamesToTimeout($nowMs + self::LOOKAHEAD_WINDOW);
            $handledGameIds = [];
            foreach ($gameIds as $gameId => $nextPlayerTurnEndsAt) {
                $timeoutIn = max(0, $nextPlayerTurnEndsAt - $nowMs);
                $estimatedSleepTime = min($estimatedSleepTime, $timeoutIn * 1000);

                if ($timeoutIn === 0) {
                    $this->commandBus->handle(new TimeoutCommand($gameId));
                    $handledGameIds[] = $gameId;
                }
            }
            $this->predisTimerStore->remove($handledGameIds);

            usleep(min(self::MAX_SLEEP, $estimatedSleepTime));
        }
    }
}
