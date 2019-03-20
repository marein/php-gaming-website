<?php
declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Exception;

final class InvalidDomainException extends MessageBrokerException
{
    /**
     * Creates a new InvalidDomainException from the domain name.
     *
     * @param string $domain
     *
     * @return InvalidDomainException
     */
    public static function fromValue(string $domain): InvalidDomainException
    {
        return new self(
            sprintf(
                'Domain should be PascalCase. "%s" given.',
                $domain
            )
        );
    }
}
