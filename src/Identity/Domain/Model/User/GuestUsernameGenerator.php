<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\User;

final class GuestUsernameGenerator
{
    /**
     * Possible usernames: 4^58 = ~11 million
     * Collisions per non-registered active users: 1,000 = ~0.04%; 2,000 = ~0.2%; 5,000 = ~1%
     *
     * Doesn't need to be unique on the whole set, because we don't rely on it for identification.
     * It's just for display purposes and to make the experience a bit more personal. Also, the chance
     * that users will meet each other is relatively low, because they will leave the page at some point.
     * If the number of non-registered users increases significantly, we can increase the length to gain more space.
     */
    private const string CHARACTER_SET = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
    private const int USERNAME_LENGTH = 4;
    private const string USERNAME_PREFIX = 'guest#';

    public static function forUserId(UserId $userId): string
    {
        $characterSetLength = strlen(self::CHARACTER_SET);
        $blockId = 0;

        $username = '';
        while (($remainingLength = self::USERNAME_LENGTH - strlen($username)) > 0) {
            $blockId++;
            $bytes = (array)unpack('J*', hash('sha256', $userId->toString() . $blockId, true));
            $username .= implode(
                '',
                array_map(
                    static fn(int $number): string => self::CHARACTER_SET[$number % $characterSetLength],
                    array_slice($bytes, 0, min($remainingLength, count($bytes)))
                )
            );
        }

        return self::USERNAME_PREFIX . $username;
    }

    public static function dummy(): string
    {
        return self::USERNAME_PREFIX . str_repeat('0', self::USERNAME_LENGTH);
    }
}
