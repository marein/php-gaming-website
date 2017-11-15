<?php

namespace Gambling\WebInterface\Presentation\Console;

use Gambling\Common\Port\Adapter\Messaging\MessageBroker;
use Gambling\WebInterface\Application\BrowserNotifier;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PublishRabbitMqEventsToNchanCommand extends Command
{
    /**
     * @var MessageBroker
     */
    private $messageBroker;

    /**
     * @var BrowserNotifier
     */
    private $browserNotifier;

    /**
     * PublishRabbitMqEventsToNchanCommand constructor.
     *
     * @param MessageBroker   $messageBroker
     * @param BrowserNotifier $browserNotifier
     */
    public function __construct(MessageBroker $messageBroker, BrowserNotifier $browserNotifier)
    {
        parent::__construct();

        $this->messageBroker = $messageBroker;
        $this->browserNotifier = $browserNotifier;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('web-interface:publish-rabbit-mq-events-to-nchan');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $eventToMethod = [
            'connect-four.game-opened'   => function (array $payload) {
                $this->browserNotifier->publish(
                    '/pub?id=lobby',
                    json_encode($payload)
                );
            },
            'connect-four.game-aborted'  => function (array $payload) {
                $this->browserNotifier->publish(
                    '/pub?id=lobby',
                    json_encode($payload)
                );
            },
            'connect-four.game-won'      => function (array $payload) {
                $this->browserNotifier->publish(
                    '/pub?id=game-' . $payload['gameId'],
                    json_encode($payload)
                );
            },
            'connect-four.game-drawn'    => function (array $payload) {
                $this->browserNotifier->publish(
                    '/pub?id=game-' . $payload['gameId'],
                    json_encode($payload)
                );
            },
            'connect-four.player-moved'  => function (array $payload) {
                $this->browserNotifier->publish(
                    '/pub?id=game-' . $payload['gameId'],
                    json_encode($payload)
                );
            },
            'connect-four.player-joined' => function (array $payload) {
                $this->browserNotifier->publish(
                    '/pub?id=lobby',
                    json_encode($payload)
                );
            },
            'connect-four.chat-assigned' => function (array $payload) {
                $this->browserNotifier->publish(
                    '/pub?id=game-' . $payload['gameId'],
                    json_encode($payload)
                );
            },
            'chat.message-written'       => function (array $payload) {
                $this->browserNotifier->publish(
                    '/pub?id=game-' . $payload['ownerId'],
                    json_encode($payload)
                );
            }
        ];

        $this->messageBroker->consume(
            'web-interface.browser-notification',
            ['connect-four.#', 'chat.message-written'],
            function (string $body, string $routingKey) use ($eventToMethod) {
                $method = $eventToMethod[$routingKey] ?? null;

                if (is_callable($method)) {
                    $payload = array_merge(
                        json_decode($body, true),
                        ['eventName' => $routingKey]
                    );

                    $method($payload);
                }
            }
        );
    }
}
