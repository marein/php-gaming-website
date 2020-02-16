<?php
declare(strict_types=1);

namespace Gaming\Identity\Port\Adapter\Console;

use Gaming\Common\EventStore\ConsistentOrderEventStore;
use Gaming\Common\EventStore\EventStore;
use Gaming\Common\EventStore\FollowEventStoreDispatcher;
use Gaming\Common\EventStore\InMemoryCacheEventStorePointer;
use Gaming\Common\EventStore\StoredEventPublisher;
use Gaming\Common\EventStore\ThrottlingEventStore;
use Gaming\Common\MessageBroker\MessageBroker;
use Gaming\Common\Port\Adapter\EventStore\PredisEventStorePointer;
use Gaming\Common\Port\Adapter\EventStore\Subscriber\SymfonyConsoleDebugSubscriber;
use Gaming\Identity\Port\Adapter\Messaging\PublishStoredEventsToRabbitMqSubscriber;
use Predis\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PublishStoredEventsToRabbitMqCommand extends Command
{
    /**
     * @var EventStore
     */
    private EventStore $eventStore;

    /**
     * @var Client
     */
    private Client $predis;

    /**
     * @var MessageBroker
     */
    private MessageBroker $messageBroker;

    /**
     * PublishStoredEventsToRabbitMqCommand constructor.
     *
     * @param EventStore    $eventStore
     * @param Client        $predis
     * @param MessageBroker $messageBroker
     */
    public function __construct(EventStore $eventStore, Client $predis, MessageBroker $messageBroker)
    {
        parent::__construct();

        $this->eventStore = $eventStore;
        $this->predis = $predis;
        $this->messageBroker = $messageBroker;
    }

    /**
     * @inheritdoc
     */
    protected function configure(): void
    {
        $this
            ->setName('identity:publish-stored-events-to-rabbit-mq');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
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
