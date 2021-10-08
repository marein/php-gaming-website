<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Model\Subscription;

interface SubscriptionTranslator
{
    /**
     * Handles the type SpecificMessage.
     *
     * @param SpecificMessage $specificMessage
     */
    public function handleSpecificMessage(SpecificMessage $specificMessage): void;

    /**
     * Handles the type WholeDomain.
     *
     * @param WholeDomain $wholeDomain
     */
    public function handleWholeDomain(WholeDomain $wholeDomain): void;
}
