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
    private ConnectFourService $connectFourService;

    private BrowserNotifier $browserNotifier;

    public function __construct(ConnectFourService $connectFourService, BrowserNotifier $browserNotifier)
    {
        parent::__construct();

        $this->connectFourService = $connectFourService;
        $this->browserNotifier = $browserNotifier;
    }

    protected function configure(): void
    {
        $this
            ->setName('web-interface:publish-running-games-count-to-nchan');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $lastRunningGamesCount = -1;

        while (true) {
            $currentRunningGamesCount = $this->connectFourService->runningGames()['count'];

            // Publish only if count has changed.
            if ($lastRunningGamesCount !== $currentRunningGamesCount) {
                $this->browserNotifier->publish(
                    '/pub?id=lobby',
                    json_encode(
                        [
                            'eventName' => 'ConnectFour.RunningGamesUpdated',
                            'count' => $currentRunningGamesCount
                        ],
                        JSON_THROW_ON_ERROR
                    )
                );

                $lastRunningGamesCount = $currentRunningGamesCount;
            }

            sleep(5);
        }
    }
}
