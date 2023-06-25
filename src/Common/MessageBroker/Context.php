<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker;

interface Context
{
    public function request(Message $message): void;

    public function reply(Message $message): void;
}
