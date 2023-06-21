<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker;

use Gaming\Common\MessageBroker\Exception\MessageBrokerException;

interface Consumer
{
    /**
     * Implementations must delegate the handling of a message to a MessageHandler.
     *
     * Implementations must dispatch MessageReceived, MessageHandled, MessageFailed,
     * ReplySent and RequestSent through a PSR-14 compliant event dispatcher.
     *
     * @param positive-int $parallelism
     *
     * @throws MessageBrokerException
     */
    public function start(int $parallelism): void;

    /**
     * @throws MessageBrokerException
     */
    public function stop(): void;
}
