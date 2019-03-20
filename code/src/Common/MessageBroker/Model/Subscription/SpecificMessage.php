<?php
declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Model\Subscription;

use Gaming\Common\MessageBroker\Exception\InvalidDomainException;
use Gaming\Common\MessageBroker\Exception\InvalidNameException;
use Gaming\Common\MessageBroker\Model\NamingConvention;

final class SpecificMessage implements Subscription
{
    /**
     * @var string
     */
    private $domain;

    /**
     * @var string
     */
    private $name;

    /**
     * SpecificMessage constructor.
     *
     * @param string $domain
     * @param string $name
     *
     * @throws InvalidDomainException
     * @throws InvalidNameException
     */
    public function __construct(string $domain, string $name)
    {
        if (!NamingConvention::isPascalCase($domain)) {
            throw InvalidDomainException::fromValue($domain);
        }

        if (!NamingConvention::isPascalCase($name)) {
            throw InvalidNameException::fromValue($domain);
        }

        $this->domain = $domain;
        $this->name = $name;
    }

    /**
     * Returns the domain name.
     *
     * @return string
     */
    public function domain(): string
    {
        return $this->domain;
    }

    /**
     * Returns the message name.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function accept(SubscriptionTranslator $subscriptionTranslator): void
    {
        $subscriptionTranslator->handleSpecificMessage($this);
    }
}
