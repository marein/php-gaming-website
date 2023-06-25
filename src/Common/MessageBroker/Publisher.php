<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker;

use Gaming\Common\MessageBroker\Exception\MessageBrokerException;

interface Publisher
{
    /**
     * As a caller of send, you must call flush after you are done sending.
     *
     * Implementations must dispatch MessageSent through a PSR-14 compliant event dispatcher.
     *
     * @throws MessageBrokerException
     */
    public function send(Message $message): void;

    /**
     * Flush can have different semantics depending on the technology used
     * and the implementation of this interface. The implementation chosen
     * should determine whether any of the following use cases apply:
     * * Make sure that all buffered messages are transferred to the server.
     * * Make sure that all received messages are saved on the disk.
     * * If the loss of messages is acceptable, the implementation can be empty.
     *
     * Implementations must dispatch MessagesFlushed and MessageReturned through a PSR-14 compliant event dispatcher.
     *
     * @throws MessageBrokerException
     */
    public function flush(): void;
}
