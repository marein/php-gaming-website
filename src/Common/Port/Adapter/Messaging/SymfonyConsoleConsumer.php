<?php

declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Messaging;

use Gaming\Common\MessageBroker\Model\Consumer\Consumer;
use Gaming\Common\MessageBroker\Model\Consumer\Name;
use Gaming\Common\MessageBroker\Model\Message\Message;
use Symfony\Component\Console\Output\OutputInterface;

final class SymfonyConsoleConsumer implements Consumer
{
    /**
     * The real consumer.
     *
     * @var Consumer
     */
    private Consumer $consumer;

    /**
     * @var OutputInterface
     */
    private OutputInterface $output;

    /**
     * SymfonyConsoleConsumer constructor.
     *
     * @param Consumer $consumer
     * @param OutputInterface $output
     */
    public function __construct(Consumer $consumer, OutputInterface $output)
    {
        $this->consumer = $consumer;
        $this->output = $output;
    }

    /**
     * @inheritdoc
     */
    public function handle(Message $message): void
    {
        $this->output->writeln(
            sprintf(
                'Received "%s" with "%s"',
                $message->name(),
                $message->body()
            )
        );

        $this->consumer->handle($message);
    }

    /**
     * @inheritdoc
     */
    public function subscriptions(): array
    {
        return $this->consumer->subscriptions();
    }

    /**
     * @inheritdoc
     */
    public function name(): Name
    {
        return $this->consumer->name();
    }
}
