<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Console;

use Gaming\Common\EventStore\ConsistentOrderEventStore;
use Gaming\Common\EventStore\EventStore;
use Gaming\Common\EventStore\FollowEventStoreDispatcher;
use Gaming\Common\EventStore\InMemoryCacheEventStorePointer;
use Gaming\Common\EventStore\StoredEventPublisher;
use Gaming\Common\EventStore\ThrottlingEventStore;
use Gaming\Common\MessageBroker\MessageBroker;
use Gaming\Common\Port\Adapter\EventStore\PredisEventStorePointer;
use Gaming\Common\Port\Adapter\EventStore\Subscriber\SymfonyConsoleDebugSubscriber;
use Gaming\ConnectFour\Port\Adapter\Messaging\PublishStoredEventsToRabbitMqSubscriber;
use Predis\ClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PublishStoredEventsToRabbitMqCommand extends Command
{
    private EventStore $eventStore;

    private ClientInterface $predis;

    private MessageBroker $messageBroker;

    public function __construct(EventStore $eventStore, ClientInterface $predis, MessageBroker $messageBroker)
    {
        parent::__construct();

        $this->eventStore = $eventStore;
        $this->predis = $predis;
        $this->messageBroker = $messageBroker;
    }

    protected function configure(): void
    {
        $this
            ->setName('connect-four:publish-stored-events-to-rabbit-mq');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // The creation of FollowEventStoreDispatcher could be done via container.
        $debugSubscriber = new SymfonyConsoleDebugSubscriber($output);
        $rabbitMqSubscriber = new PublishStoredEventsToRabbitMqSubscriber(
            $this->messageBroker
        );

        $eventStorePointer = new InMemoryCacheEventStorePointer(
            new PredisEventStorePointer(
                $this->predis,
                'rabbit-mq-pointer'
            )
        );

        $storedEventPublisher = new StoredEventPublisher();
        $storedEventPublisher->subscribe($rabbitMqSubscriber);
        $storedEventPublisher->subscribe($debugSubscriber);

        $followEventStoreDispatcher = new FollowEventStoreDispatcher(
            $eventStorePointer,
            new ThrottlingEventStore(
                new ConsistentOrderEventStore($this->eventStore),
                100
            ),
            $storedEventPublisher
        );

        while (true) {
            $followEventStoreDispatcher->dispatch(1000);
        }
    }
}
