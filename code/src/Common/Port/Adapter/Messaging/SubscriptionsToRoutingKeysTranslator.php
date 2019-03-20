<?php
declare(strict_types=1);

namespace Gaming\Common\Port\Adapter\Messaging;

use Gaming\Common\MessageBroker\Model\Subscription\SpecificMessage;
use Gaming\Common\MessageBroker\Model\Subscription\Subscription;
use Gaming\Common\MessageBroker\Model\Subscription\SubscriptionTranslator;
use Gaming\Common\MessageBroker\Model\Subscription\WholeDomain;

/**
 * This class maps each object to its equivalent routing key for rabbit mq.
 */
final class SubscriptionsToRoutingKeysTranslator implements SubscriptionTranslator
{
    /**
     * @var string[]
     */
    private $routingKeys;

    /**
     * SubscriptionsToRoutingKeysTranslator constructor.
     *
     * @param Subscription[] $subscriptions
     */
    public function __construct(array $subscriptions)
    {
        $this->routingKeys = [];

        foreach ($subscriptions as $subscription) {
            $subscription->accept($this);
        }
    }

    /**
     * @return string[]
     */
    public function routingKeys(): array
    {
        return $this->routingKeys;
    }

    /**
     * @inheritdoc
     */
    public function handleWholeDomain(WholeDomain $wholeDomain): void
    {
        $this->routingKeys[] = $wholeDomain->domain() . '.#';
    }

    /**
     * @inheritdoc
     */
    public function handleSpecificMessage(SpecificMessage $specificMessage): void
    {
        $this->routingKeys[] = $specificMessage->domain() . '.' . $specificMessage->name();
    }
}
