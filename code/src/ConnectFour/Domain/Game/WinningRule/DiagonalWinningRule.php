<?php

namespace Gambling\ConnectFour\Domain\Game\WinningRule;

use Gambling\ConnectFour\Domain\Game\Board\Board;
use Gambling\ConnectFour\Domain\Game\Exception\InvalidNumberOfRequiredMatchesException;

final class DiagonalWinningRule implements WinningRule
{
    const MINIMUM = 4;

    /**
     * @var int
     */
    private $numberOfRequiredMatches;

    /**
     * DiagonalWinningRule constructor.
     *
     * @param int $numberOfRequiredMatches
     *
     * @throws InvalidNumberOfRequiredMatchesException
     */
    public function __construct(int $numberOfRequiredMatches)
    {
        if ($numberOfRequiredMatches < self::MINIMUM) {
            throw new InvalidNumberOfRequiredMatchesException('The value must be at least ' . self::MINIMUM . '.');
        }

        $this->numberOfRequiredMatches = $numberOfRequiredMatches;
    }

    /**
     * @inheritdoc
     */
    public function calculate(Board $board): bool
    {
        if (!$board->lastUsedField()) {
            return false;
        }

        $stone = $board->lastUsedField()->stone();
        $point = $board->lastUsedField()->point();

        $fields1 = $board->findFieldsInMainDiagonalByPoint($point);
        $fields2 = $board->findFieldsInCounterDiagonalByPoint($point);

        // Create a string representation of fields e.g. "000121 000121"
        $haystack = implode($fields1) . ' ' . implode($fields2);
        // Create a string like "1111|2222" depending on the stone and the required matches.
        $needle = str_repeat((string)$stone->color(), $this->numberOfRequiredMatches);

        // Check whether "1111|2222" is in "000121 000121"
        return strpos($haystack, $needle) !== false;
    }
}
