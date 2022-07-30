<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\EventStore\Subscriber;

use Gaming\Common\Domain\DomainEvent;
use Gaming\Common\EventStore\NoCommit;
use Gaming\Common\EventStore\StoredEvent;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\Common\Normalizer\Normalizer;
use Symfony\Component\Console\Output\OutputInterface;

final class SymfonyConsoleDebugSubscriber implements StoredEventSubscriber
{
    use NoCommit;

    public function __construct(
        private readonly OutputInterface $output,
        private readonly Normalizer $normalizer
    ) {
    }

    public function handle(StoredEvent $storedEvent): void
    {
        $domainEvent = $storedEvent->domainEvent();

        $this->output->writeln(
            sprintf(
                'Received #%s "%s" with "%s"',
                $storedEvent->id(),
                $domainEvent::class,
                json_encode(
                    $this->normalizer->normalize($domainEvent, DomainEvent::class),
                    JSON_THROW_ON_ERROR
                )
            )
        );
    }
}
