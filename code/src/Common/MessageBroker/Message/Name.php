<?php
declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Message;

use Gaming\Common\MessageBroker\Exception\InvalidDomainException;
use Gaming\Common\MessageBroker\Exception\InvalidFormatException;
use Gaming\Common\MessageBroker\Exception\InvalidNameException;
use Gaming\Common\MessageBroker\Exception\MessageBrokerException;

/**
 * This class defines a standard of what a message name should look like.
 */
final class Name
{
    private const PART_PATTERN = '
        /^
            (?:                 # Start of non capturing parenthesis.
                [A-Z]           # Must have uppercase letter.
                [a-z]+          # Followed by at least one lowercase letter.
            )+                  # This pattern at least once.
        $/x
    ';

    /**
     * @var string
     */
    private $domain;

    /**
     * @var string
     */
    private $name;

    /**
     * Name constructor.
     *
     * @param string $domain
     * @param string $name
     *
     * @throws InvalidDomainException
     * @throws InvalidNameException
     */
    public function __construct(string $domain, string $name)
    {
        if (!preg_match(self::PART_PATTERN, $domain)) {
            throw new InvalidDomainException(
                sprintf(
                    'Domain should be PascalCase. "%s" given.',
                    $domain
                )
            );
        }

        if (!preg_match(self::PART_PATTERN, $name)) {
            throw new InvalidNameException(
                sprintf(
                    'Name should be PascalCase. "%s" given.',
                    $name
                )
            );
        }

        $this->domain = $domain;
        $this->name = $name;
    }

    /**
     * Creates a message name from string.
     *
     * @param string $messageName
     *
     * @return Name
     * @throws InvalidDomainException
     * @throws InvalidNameException
     * @throws MessageBrokerException
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

    /**
     * @return string
     */
    public function domain(): string
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf(
            '%s.%s',
            $this->domain,
            $this->name
        );
    }
}
