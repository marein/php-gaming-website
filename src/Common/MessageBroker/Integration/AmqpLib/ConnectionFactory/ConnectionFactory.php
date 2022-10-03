<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\ConnectionFactory;

use Gaming\Common\MessageBroker\Exception\MessageBrokerException;
use PhpAmqpLib\Connection\AbstractConnection;

interface ConnectionFactory
{
    /**
     * @throws MessageBrokerException
     */
    public function create(): AbstractConnection;
}
