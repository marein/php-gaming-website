<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Console;

use Gaming\Common\BrowserNotifier\BrowserNotifier;
use Gaming\Common\Bus\Bus;
use Gaming\ConnectFour\Application\Game\Query\RunningGamesQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PublishRunningGamesCountToNchanCommand extends Command
{
    public function __construct(
        private readonly Bus $connectFourQueryBus,
        private readonly BrowserNotifier $browserNotifier
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $lastRunningGamesCount = -1;

        while (true) {
            $currentRunningGamesCount = $this->connectFourQueryBus->handle(new RunningGamesQuery())->count();

            // Publish only if count has changed.
            if ($lastRunningGamesCount !== $currentRunningGamesCount) {
                $this->browserNotifier->publish(
                    ['lobby'],
                    'ConnectFour.RunningGamesUpdated',
                    json_encode(['count' => $currentRunningGamesCount], JSON_THROW_ON_ERROR)
                );

                $lastRunningGamesCount = $currentRunningGamesCount;
            }

            sleep(5);
        }
    }
}
