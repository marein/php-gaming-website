<?php

namespace Gambling\WebInterface\Presentation\Console;

use Gambling\WebInterface\Application\BrowserNotifier;
use Gambling\WebInterface\Application\ConnectFourService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PublishRunningGamesCountToNchanCommand extends Command
{
    /**
     * @var ConnectFourService
     */
    private $connectFourService;

    /**
     * @var BrowserNotifier
     */
    private $browserNotifier;

    /**
     * PublishRunningGamesCountToNchanCommand constructor.
     *
     * @param ConnectFourService $connectFourService
     * @param BrowserNotifier    $browserNotifier
     */
    public function __construct(ConnectFourService $connectFourService, BrowserNotifier $browserNotifier)
    {
        parent::__construct();

        $this->connectFourService = $connectFourService;
        $this->browserNotifier = $browserNotifier;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('web-interface:publish-running-games-count-to-nchan');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lastRunningGamesCount = -1;

        while (true) {
            $currentRunningGamesCount = $this->connectFourService->runningGames()['count'];

            // Publish only if count has changed.
            if ($lastRunningGamesCount !== $currentRunningGamesCount) {
                $this->browserNotifier->publish(
                    '/pub?id=lobby',
                    json_encode([
                        'eventName' => 'connect-four.running-games-updated',
                        'count'     => $currentRunningGamesCount
                    ])
                );

                $lastRunningGamesCount = $currentRunningGamesCount;
            }

            sleep(5);
        }
    }
}
