<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\WinningRule;

use Gaming\ConnectFour\Domain\Game\Board\Board;
use Gaming\ConnectFour\Domain\Game\Exception\InvalidNumberOfRequiredMatchesException;

final class VerticalWinningRule implements WinningRule
{
    private const MINIMUM = 4;

    private int $numberOfRequiredMatches;

    /**
     * @throws InvalidNumberOfRequiredMatchesException
     */
    public function __construct(int $numberOfRequiredMatches)
    {
        if ($numberOfRequiredMatches < self::MINIMUM) {
            throw new InvalidNumberOfRequiredMatchesException('The value must be at least ' . self::MINIMUM . '.');
        }

        $this->numberOfRequiredMatches = $numberOfRequiredMatches;
    }

    public function calculate(Board $board): ?array
    {
        if ($board->lastUsedField()->isEmpty()) {
            return null;
        }

        $stone = $board->lastUsedField()->stone();
        $point = $board->lastUsedField()->point();
        $fields = $board->findFieldsByColumn($point->x());

        // Create a string representation of fields e.g. "000121" and find the start position of a winning sequence.
        $start = strpos(
            implode($fields),
            str_repeat((string)$stone->value, $this->numberOfRequiredMatches)
        );

        return $start !== false ? array_slice($fields, $start, $this->numberOfRequiredMatches) : null;
    }
}
