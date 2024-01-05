<?php

declare(strict_types=1);

namespace Gaming\WebInterface\Presentation\Console;

use Gaming\WebInterface\Application\BrowserNotifier;
use Gaming\WebInterface\Application\ConnectFourService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PublishRunningGamesCountToNchanCommand extends Command
{
    public function __construct(
        private readonly ConnectFourService $connectFourService,
        private readonly BrowserNotifier $browserNotifier
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $lastRunningGamesCount = -1;

        while (true) {
            $currentRunningGamesCount = $this->connectFourService->runningGames()['count'];

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
