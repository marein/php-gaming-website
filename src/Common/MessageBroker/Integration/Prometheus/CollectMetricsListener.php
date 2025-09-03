<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\Prometheus;

use Gaming\Common\MessageBroker\Event\MessageFailed;
use Gaming\Common\MessageBroker\Event\MessageHandled;
use Gaming\Common\MessageBroker\Event\MessageReceived;
use Prometheus\RegistryInterface;

final class CollectMetricsListener
{
    /**
     * Messages might be handled asynchronously, so we need to maintain a map of messages
     * until they are handled to be able to calculate the duration.
     *
     * @var array<string, float>
     */
    private array $receivedMessages = [];

    public function __construct(
        private readonly RegistryInterface $registry,
        private readonly string $metricsNamespace,
        private readonly string $topicKey = 'queue'
    ) {
    }

    public function messageReceived(MessageReceived $event): void
    {
        $this->receivedMessages[spl_object_hash($event->message)] = microtime(true);
    }

    public function messageHandled(MessageHandled $event): void
    {
        $this->registry->getOrRegisterCounter(
            $this->metricsNamespace,
            'messaging_messages_total',
            'Total number of messages processed.',
            ['topic', 'message']
        )->inc(
            [
                $event->metadata[$this->topicKey] ?? 'unknown',
                $event->message->name()
            ]
        );

        $this->addToHistogram($event, 'handled');
    }

    public function messageFailed(MessageFailed $event): void
    {
        $this->addToHistogram($event, 'failed');
    }

    private function addToHistogram(MessageHandled|MessageFailed $event, string $result): void
    {
        $messageKey = spl_object_hash($event->message);

        $messageReceivedAt = $this->receivedMessages[$messageKey] ?? null;
        if ($messageReceivedAt === null) {
            return;
        }
        unset($this->receivedMessages[$messageKey]);

        $this->registry->getOrRegisterHistogram(
            $this->metricsNamespace,
            'messaging_message_duration_seconds',
            'Message processing latencies in seconds.',
            ['topic', 'message', 'result'],
            [0.01, 0.02, 0.03, 0.04, 0.05, 0.075, 0.1, 0.25, 0.5, 2]
        )->observe(
            microtime(true) - $messageReceivedAt,
            [
                $event->metadata[$this->topicKey] ?? 'unknown',
                $event->message->name(),
                $result
            ]
        );
    }
}
