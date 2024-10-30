<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\WinningRule;

use Gaming\ConnectFour\Domain\Game\Board\Board;
use Gaming\ConnectFour\Domain\Game\Board\Field;
use Gaming\ConnectFour\Domain\Game\Board\Point;
use Gaming\ConnectFour\Domain\Game\Exception\WinningSequenceLengthTooShortException;

abstract class SequenceBasedWinningRule implements WinningRule
{
    private const int MINIMUM = 4;

    /**
     * @throws WinningSequenceLengthTooShortException
     */
    public function __construct(
        private readonly int $winningSequenceLength
    ) {
        if ($winningSequenceLength < self::MINIMUM) {
            throw new WinningSequenceLengthTooShortException('The value must be at least ' . self::MINIMUM . '.');
        }
    }

    public function findWinningSequence(Board $board): ?WinningSequence
    {
        if ($board->lastUsedField()->isEmpty()) {
            return null;
        }

        $stone = $board->lastUsedField()->stone();
        $point = $board->lastUsedField()->point();
        $winningSequence = str_repeat((string)$stone->value, $this->winningSequenceLength);
        $fields = $this->findFields($board, $point);

        $winningSequencePosition = strpos(implode($fields), $winningSequence);
        if ($winningSequencePosition === false) {
            return null;
        }

        return new WinningSequence(
            $this->name(),
            array_map(
                static fn(Field $field) => $field->point(),
                array_slice($fields, $winningSequencePosition, $this->winningSequenceLength)
            )
        );
    }

    /**
     * @return Field[]
     */
    abstract protected function findFields(Board $board, Point $point): array;

    abstract protected function name(): string;
}
