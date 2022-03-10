<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker;

use Gaming\Common\MessageBroker\Exception\MessageBrokerException;
use Gaming\Common\MessageBroker\Model\Message\Message;

interface Publisher
{
    /**
     * @throws MessageBrokerException
     */
    public function publish(Message $message): void;

    /**
     * @throws MessageBrokerException
     */
    public function flush(): void;
}
