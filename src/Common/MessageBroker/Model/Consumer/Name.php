<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Model\Consumer;

use Gaming\Common\MessageBroker\Exception\InvalidDomainException;
use Gaming\Common\MessageBroker\Exception\InvalidNameException;
use Gaming\Common\MessageBroker\Model\NamingConvention;

final class Name
{
    private string $domain;

    private string $name;

    /**
     * @throws InvalidDomainException
     * @throws InvalidNameException
     */
    public function __construct(string $domain, string $name)
    {
        NamingConvention::verifyDomainName($domain);
        NamingConvention::verifyConsumerName($name);

        $this->domain = $domain;
        $this->name = $name;
    }

    public function domain(): string
    {
        return $this->domain;
    }

    public function name(): string
    {
        return $this->name;
    }
}
