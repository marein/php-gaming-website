<?php

namespace Gambling\ConnectFour\Port\Adapter\Console;

use Gambling\Common\Bus\Bus;
use Gambling\Common\Port\Adapter\Messaging\MessageBroker;
use Gambling\ConnectFour\Application\Game\Command\AssignChatCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class RefereeCommand extends Command
{
    /**
     * @var MessageBroker
     */
    private $messageBroker;

    /**
     * @var Bus
     */
    private $commandBus;

    /**
     * RefereeCommand constructor.
     *
     * @param MessageBroker $messageBroker
     * @param Bus           $commandBus
     */
    public function __construct(MessageBroker $messageBroker, Bus $commandBus)
    {
        parent::__construct();

        $this->messageBroker = $messageBroker;
        $this->commandBus = $commandBus;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('connect-four:referee');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $commandOrEventToMethod = [
            'chat.chat-initiated'        => function (array $payload) {
                $this->commandBus->handle(
                    new AssignChatCommand(
                        $payload['ownerId'],
                        $payload['chatId']
                    )
                );
            },
            'connect-four.player-joined' => function (array $payload) {
                $this->messageBroker->publish(
                    json_encode([
                        'ownerId' => $payload['gameId'],
                        'authors' => []
                    ]),
                    'chat.initiate-chat'
                );
            }
        ];

        $this->messageBroker->consume(
            'connect-four.referee',
            array_keys($commandOrEventToMethod),
            function (string $body, string $routingKey) use ($commandOrEventToMethod) {
                $method = $commandOrEventToMethod[$routingKey] ?? null;
                $payload = json_decode($body, true);

                $method($payload);
            }
        );
    }
}
