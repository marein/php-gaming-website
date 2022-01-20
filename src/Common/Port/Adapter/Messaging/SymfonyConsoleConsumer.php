<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Messaging;

use Gaming\Common\MessageBroker\Model\Consumer\Consumer;
use Gaming\Common\MessageBroker\Model\Consumer\Name;
use Gaming\Common\MessageBroker\Model\Context\Context;
use Gaming\Common\MessageBroker\Model\Message\Message;
use Symfony\Component\Console\Output\OutputInterface;

final class SymfonyConsoleConsumer implements Consumer
{
    private Consumer $consumer;

    private OutputInterface $output;

    public function __construct(Consumer $consumer, OutputInterface $output)
    {
        $this->consumer = $consumer;
        $this->output = $output;
    }

    public function handle(Message $message, Context $context): void
    {
        $this->output->writeln(
            sprintf(
                'Received "%s" with "%s"',
                $message->name(),
                $message->body()
            )
        );

        $this->consumer->handle($message, $context);
    }

    public function subscriptions(): array
    {
        return $this->consumer->subscriptions();
    }

    public function name(): Name
    {
        return $this->consumer->name();
    }
}
