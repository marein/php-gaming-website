<?php

declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Model;

use Gaming\Common\MessageBroker\Exception\InvalidDomainException;
use Gaming\Common\MessageBroker\Exception\InvalidNameException;

/**
 * This class is used as an implementation detail
 * in other classes within the MessageBroker namespace.
 */
final class NamingConvention
{
    private const PASCAL_CASE_PATTERN = '
        /^
            (?:         # Start of non capturing parenthesis.
                [A-Z]   # Must have uppercase letter.
                [a-z]+  # Followed by at least one lowercase letter.
            )+          # This pattern at least once.
        $/x
    ';

    /**
     * @throws InvalidNameException
     */
    public static function verifyConsumerName(string $name): void
    {
        if (!preg_match(self::PASCAL_CASE_PATTERN, $name)) {
            throw new InvalidNameException(
                sprintf(
                    'Consumer name should be PascalCase. "%s" given.',
                    $name
                )
            );
        }
    }

    /**
     * @throws InvalidDomainException
     */
    public static function verifyDomainName(string $domain): void
    {
        if (!preg_match(self::PASCAL_CASE_PATTERN, $domain)) {
            throw new InvalidDomainException(
                sprintf(
                    'Domain name should be PascalCase. "%s" given.',
                    $domain
                )
            );
        }
    }

    /**
     * @throws InvalidNameException
     */
    public static function verifyMessageName(string $name): void
    {
        if (!preg_match(self::PASCAL_CASE_PATTERN, $name)) {
            throw new InvalidNameException(
                sprintf(
                    'Message name should be PascalCase. "%s" given.',
                    $name
                )
            );
        }
    }
}
