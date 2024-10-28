<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\WinningRule;

use Gaming\ConnectFour\Domain\Game\Board\Board;
use Gaming\ConnectFour\Domain\Game\Board\Field;
use Gaming\ConnectFour\Domain\Game\Board\Point;
use Gaming\ConnectFour\Domain\Game\Exception\InvalidNumberOfRequiredMatchesException;

abstract class SequenceBasedWinningRule implements WinningRule
{
    private const int MINIMUM = 4;

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

    public function findWinningSequence(Board $board): array
    {
        if ($board->lastUsedField()->isEmpty()) {
            return [];
        }

        $stone = $board->lastUsedField()->stone();
        $point = $board->lastUsedField()->point();
        $winningSequence = str_repeat((string)$stone->value, $this->numberOfRequiredMatches);
        $fields = $this->findFields($board, $point);

        $winningSequencePosition = strpos(implode($fields), $winningSequence);
        if ($winningSequencePosition === false) {
            return [];
        }

        return array_map(
            static fn(Field $field) => $field->point(),
            array_slice($fields, $winningSequencePosition, $this->numberOfRequiredMatches)
        );
    }

    /**
     * @return Field[]
     */
    abstract protected function findFields(Board $board, Point $point): array;
}
