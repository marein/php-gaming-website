<?php
declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Model\Consumer;

use Gaming\Common\MessageBroker\Model\Message\Message;
use Gaming\Common\MessageBroker\Model\Subscription\Subscription;

interface Consumer
{
    /**
     * Handle the message.
     *
     * @param Message $message
     */
    public function handle(Message $message): void;

    /**
     * Define what this consumer subscribes to.
     *
     * @return Subscription[]
     */
    public function subscriptions(): array;

    /**
     * The name for this consumer.
     *
     * @return Name
     */
    public function name(): Name;
}
