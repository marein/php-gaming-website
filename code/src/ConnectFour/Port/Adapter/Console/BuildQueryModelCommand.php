<?php

namespace Gambling\ConnectFour\Port\Adapter\Console;

use Gambling\Common\EventStore\EventStore;
use Gambling\Common\EventStore\FollowEventStoreDispatcher;
use Gambling\Common\EventStore\InMemoryCacheEventStorePointer;
use Gambling\Common\Port\Adapter\EventStore\PredisEventStorePointer;
use Gambling\Common\EventStore\StoredEventPublisher;
use Gambling\Common\Port\Adapter\EventStore\Subscriber\SymfonyConsoleDebugSubscriber;
use Gambling\ConnectFour\Port\Adapter\Persistence\Projection\PredisGameProjection;
use Gambling\ConnectFour\Port\Adapter\Persistence\Projection\PredisGamesByPlayerProjection;
use Gambling\ConnectFour\Port\Adapter\Persistence\Projection\PredisOpenGamesProjection;
use Gambling\ConnectFour\Port\Adapter\Persistence\Projection\PredisRunningGamesProjection;
use Predis\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class BuildQueryModelCommand extends Command
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var Client
     */
    private $predis;

    /**
     * BuildQueryModelCommand constructor.
     *
     * @param EventStore $eventStore
     * @param Client     $predis
     */
    public function __construct(EventStore $eventStore, Client $predis)
    {
        parent::__construct('connect-four:build-query-model');

        $this->eventStore = $eventStore;
        $this->predis = $predis;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // The creation of FollowEventStoreDispatcher could be done via container.
        $runningGamesProjection = new PredisRunningGamesProjection($this->predis);
        $openGamesProjection = new PredisOpenGamesProjection($this->predis);
        $gamesByPlayerProjection = new PredisGamesByPlayerProjection($this->predis);
        $gameProjection = new PredisGameProjection($this->predis);
        $debugSubscriber = new SymfonyConsoleDebugSubscriber($output);

        $publishedStoredEventTracker = new InMemoryCacheEventStorePointer(
            new PredisEventStorePointer(
                $this->predis,
                'query-model-builder-pointer'
            )
        );

        $storedEventPublisher = new StoredEventPublisher();
        $storedEventPublisher->subscribe($runningGamesProjection);
        $storedEventPublisher->subscribe($openGamesProjection);
        $storedEventPublisher->subscribe($gamesByPlayerProjection);
        $storedEventPublisher->subscribe($gameProjection);
        $storedEventPublisher->subscribe($debugSubscriber);

        $followEventStoreDispatcher = new FollowEventStoreDispatcher(
            $publishedStoredEventTracker,
            $this->eventStore,
            $storedEventPublisher
        );

        while (true) {
            $followEventStoreDispatcher->dispatch(1000);

            sleep(2);
        }
    }
}
