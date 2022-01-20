<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Model\Context;

use Gaming\Common\MessageBroker\Model\Message\Message;

interface Context
{
    public function publish(Message $message): void;

    public function reply(Message $message): void;
}
