<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Integration\Symfony;

use Gaming\Common\MessageBroker\Event\MessageHandled;
use Symfony\Contracts\Service\ResetInterface;

final class ResetServicesListener
{
    private int $numberOfHandledMessages;

    public function __construct(
        private readonly ResetInterface $resettable,
        private readonly int $everyNthMessage
    ) {
        $this->numberOfHandledMessages = 0;
    }

    public function messageHandled(MessageHandled $event): void
    {
        if (++$this->numberOfHandledMessages % $this->everyNthMessage === 0) {
            $this->resettable->reset();
        }
    }
}
