<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\AmqpLib\MessageRouter;

use Gaming\Common\MessageBroker\Message;

interface MessageRouter
{
    public function route(Message $message): Route;
}
