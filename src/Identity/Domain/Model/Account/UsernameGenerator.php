<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\Account;

final class UsernameGenerator
{
    /**
     * 7 verbs * 7 nouns * 58^4 = 554,508,304 combinations that yield ~9 collisions for 100,000 non-registered accounts.
     *
     * Doesn't need to be unique on the whole set, because we don't rely on it for identification.
     * It's just for display purposes and to make the experience a bit more personal. Also, the chance
     * that accounts will meet each other is relatively low, because they will leave the page at some point.
     * If the number of non-registered accounts increases significantly, the tag length can be increased.
     */
    private const string TAG_CHARACTER_SET = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
    private const int TAG_LENGTH = 4;
    private const string USERNAME_SEPARATOR = '#';
    public const array USERNAME_VERBS = ['Rolling', 'Laughing', 'Fighting', 'Running', 'Dancing', 'Jumping', 'Flying'];
    public const array USERNAME_NOUNS = ['Meeple', 'Pawn', 'Knight', 'Bishop', 'Rook', 'Queen', 'King'];

    public static function forAccountId(AccountId $accountId): string
    {
        $bytes = (array)unpack('Nverb/Nnoun', hash('sha256', $accountId->toString(), true));
        $username = sprintf(
            '%s%s',
            self::USERNAME_VERBS[$bytes['verb'] % count(self::USERNAME_VERBS)],
            self::USERNAME_NOUNS[$bytes['noun'] % count(self::USERNAME_NOUNS)]
        );

        $tagCharacterSetLength = strlen(self::TAG_CHARACTER_SET);
        $blockId = 0;
        $tag = '';
        while (($remainingLength = self::TAG_LENGTH - strlen($tag)) > 0) {
            $blockId++;
            $bytes = (array)unpack('N*', hash('sha256', $accountId->toString() . $blockId, true));
            $tag .= implode(
                '',
                array_map(
                    static fn(int $number): string => self::TAG_CHARACTER_SET[$number % $tagCharacterSetLength],
                    array_slice($bytes, 0, min($remainingLength, count($bytes)))
                )
            );
        }

        return $username . self::USERNAME_SEPARATOR . $tag;
    }

    public static function dummy(): string
    {
        return 'Guest' . self::USERNAME_SEPARATOR . str_repeat('0', self::TAG_LENGTH);
    }
}
