<?php
declare(strict_types=1);

namespace Gaming\Common\MessageBroker\Model;

/**
 * This class is used as an implementation detail
 * in other classes within the MessageBroker namespace.
 */
final class NamingConvention
{
    /**
     * Returns true if the specified string is in pascal case notation, false otherwise.
     *
     * @param string $value
     *
     * @return bool
     */
    public static function isPascalCase(string $value): bool
    {
        return (bool)preg_match(
            '
                /^
                    (?:         # Start of non capturing parenthesis.
                        [A-Z]   # Must have uppercase letter.
                        [a-z]+  # Followed by at least one lowercase letter.
                    )+          # This pattern at least once.
                $/x
            ',
            $value
        );
    }
}
