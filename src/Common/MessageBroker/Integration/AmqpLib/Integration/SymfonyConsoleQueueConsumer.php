<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\Integration;

use Closure;
use Gaming\Common\MessageBroker\Context;
use Gaming\Common\MessageBroker\Integration\AmqpLib\QueueConsumer\CallbackFactory;
use Gaming\Common\MessageBroker\Integration\AmqpLib\QueueConsumer\QueueConsumer;
use Gaming\Common\MessageBroker\Message;
use Gaming\Common\MessageBroker\MessageHandler;
use PhpAmqpLib\Channel\AMQPChannel;
use Symfony\Component\Console\Output\OutputInterface;

final readonly class SymfonyConsoleQueueConsumer implements QueueConsumer
{
    public function __construct(
        private QueueConsumer $queueConsumer,
        private OutputInterface $output
    ) {
    }

    public function register(AMQPChannel $channel, CallbackFactory $callbackFactory): void
    {
        $this->queueConsumer->register(
            $channel,
            $this->decorateCallbackFactory($callbackFactory)
        );
    }

    private function decorateCallbackFactory(CallbackFactory $callbackFactory): CallbackFactory
    {
        return new class($callbackFactory, $this->output) implements CallbackFactory {
            public function __construct(
                private readonly CallbackFactory $callbackFactory,
                private readonly OutputInterface $output
            ) {
            }

            public function create(string $queueName, MessageHandler $messageHandler): Closure
            {
                $this->output->writeln(
                    sprintf(
                        'Consume from queue "%s"',
                        $queueName
                    )
                );

                return $this->callbackFactory->create(
                    $queueName,
                    $this->decorateMessageHandler($messageHandler, $queueName)
                );
            }

            private function decorateMessageHandler(MessageHandler $messageHandler, string $queueName): MessageHandler
            {
                return new class($messageHandler, $queueName, $this->output) implements MessageHandler {
                    public function __construct(
                        private readonly MessageHandler $messageHandler,
                        private readonly string $queueName,
                        private readonly OutputInterface $output
                    ) {
                    }

                    public function handle(Message $message, Context $context): void
                    {
                        $this->output->writeln(
                            sprintf(
                                '"%s" handled "%s" with "%s"',
                                $this->queueName,
                                $message->name(),
                                $message->body()
                            )
                        );

                        $this->messageHandler->handle($message, $this->decorateContext($context, $this->queueName));
                    }

                    private function decorateContext(Context $context, string $queueName): Context
                    {
                        return new class($context, $queueName, $this->output) implements Context {
                            public function __construct(
                                private readonly Context $context,
                                private readonly string $queueName,
                                private readonly OutputInterface $output
                            ) {
                            }

                            public function request(Message $message): void
                            {
                                $this->output->writeln(
                                    sprintf(
                                        '"%s" requested "%s" with "%s"',
                                        $this->queueName,
                                        $message->name(),
                                        $message->body()
                                    )
                                );

                                $this->context->request($message);
                            }

                            public function reply(Message $message): void
                            {
                                $this->output->writeln(
                                    sprintf(
                                        '"%s" replied "%s" with "%s"',
                                        $this->queueName,
                                        $message->name(),
                                        $message->body()
                                    )
                                );

                                $this->context->reply($message);
                            }
                        };
                    }
                };
            }
        };
    }
}
