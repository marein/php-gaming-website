<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker;

interface MessageHandler
{
    public function handle(Message $message, Context $context): void;
}
