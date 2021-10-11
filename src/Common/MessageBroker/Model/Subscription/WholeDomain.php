<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Model\Subscription;

use Gaming\Common\MessageBroker\Exception\InvalidDomainException;
use Gaming\Common\MessageBroker\Model\NamingConvention;

final class WholeDomain implements Subscription
{
    private string $domain;

    /**
     * @throws InvalidDomainException
     */
    public function __construct(string $domain)
    {
        NamingConvention::verifyDomainName($domain);

        $this->domain = $domain;
    }

    public function domain(): string
    {
        return $this->domain;
    }

    public function accept(SubscriptionTranslator $subscriptionTranslator): void
    {
        $subscriptionTranslator->handleWholeDomain($this);
    }
}
