<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Model\Subscription;

interface Subscription
{
    /**
     * The implementation must call a SubscriptionTranslator method
     * so that the SubscriptionTranslator knows what type the implementation has.
     *
     * @param SubscriptionTranslator $subscriptionTranslator
     */
    public function accept(SubscriptionTranslator $subscriptionTranslator): void;
}
