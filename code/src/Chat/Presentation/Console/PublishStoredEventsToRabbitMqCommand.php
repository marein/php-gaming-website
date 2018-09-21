<?php
declare(strict_types=1);

namespace Gambling\Chat\Presentation\Console;

use Gambling\Chat\Infrastructure\Messaging\PublishStoredEventsToRabbitMqSubscriber;
use Gambling\Common\EventStore\ConsistentOrderEventStore;
use Gambling\Common\EventStore\EventStore;
use Gambling\Common\EventStore\FollowEventStoreDispatcher;
use Gambling\Common\EventStore\InMemoryCacheEventStorePointer;
use Gambling\Common\EventStore\StoredEventPublisher;
use Gambling\Common\EventStore\ThrottlingEventStore;
use Gambling\Common\MessageBroker\MessageBroker;
use Gambling\Common\Port\Adapter\EventStore\PredisEventStorePointer;
use Gambling\Common\Port\Adapter\EventStore\Subscriber\SymfonyConsoleDebugSubscriber;
use Predis\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PublishStoredEventsToRabbitMqCommand extends Command
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
     * @var MessageBroker
     */
    private $messageBroker;

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
    protected function configure()
    {
        $this
            ->setName('chat:publish-stored-events-to-rabbit-mq');
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
