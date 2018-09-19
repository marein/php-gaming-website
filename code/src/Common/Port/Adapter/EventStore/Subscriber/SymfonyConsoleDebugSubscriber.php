<?php
declare(strict_types=1);

namespace Gambling\Common\Port\Adapter\EventStore\Subscriber;

use Gambling\Common\EventStore\StoredEvent;
use Gambling\Common\EventStore\StoredEventSubscriber;
use Symfony\Component\Console\Output\OutputInterface;

final class SymfonyConsoleDebugSubscriber implements StoredEventSubscriber
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * SymfonyConsoleDebugSubscriber constructor.
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * @inheritdoc
     */
    public function handle(StoredEvent $storedEvent): void
    {
        $this->output->writeln(sprintf(
            'Received #%s "%s" with "%s"',
            $storedEvent->id(),
            $storedEvent->name(),
            $storedEvent->payload()
        ));
    }

    /**
     * @inheritdoc
     */
    public function isSubscribedTo(StoredEvent $storedEvent): bool
    {
        return true;
    }
}
