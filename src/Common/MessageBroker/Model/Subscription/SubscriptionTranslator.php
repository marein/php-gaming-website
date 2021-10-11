<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Model\Subscription;

interface SubscriptionTranslator
{
    public function handleSpecificMessage(SpecificMessage $specificMessage): void;

    public function handleWholeDomain(WholeDomain $wholeDomain): void;
}
