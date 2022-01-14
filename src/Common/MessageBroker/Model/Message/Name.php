<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Model\Message;

use Gaming\Common\MessageBroker\Exception\InvalidDomainException;
use Gaming\Common\MessageBroker\Exception\InvalidFormatException;
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
        NamingConvention::verifyMessageName($name);

        $this->domain = $domain;
        $this->name = $name;
    }

    /**
     * @throws InvalidDomainException
     * @throws InvalidFormatException
     * @throws InvalidNameException
     */
    public static function fromString(string $messageName): Name
    {
        $messageParts = explode('.', $messageName);

        if (count($messageParts) !== 2) {
            throw new InvalidFormatException('Name format must be "[domain].[name]".');
        }

        [$domain, $name] = $messageParts;

        return new Name($domain, $name);
    }

    public function domain(): string
    {
        return $this->domain;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return sprintf(
            '%s.%s',
            $this->domain,
            $this->name
        );
    }
}
