<?php
declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Exception;

final class InvalidNameException extends MessageBrokerException
{
    /**
     * Creates a new InvalidNameException from the message name.
     *
     * @param string $name
     *
     * @return InvalidNameException
     */
    public static function fromValue(string $name): InvalidNameException
    {
        return new self(
            sprintf(
                'Name should be PascalCase. "%s" given.',
                $name
            )
        );
    }
}
