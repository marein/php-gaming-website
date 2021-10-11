<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Board;

use Gaming\ConnectFour\Domain\Game\Exception\ColumnAlreadyFilledException;
use Gaming\ConnectFour\Domain\Game\Exception\OutOfSizeException;

final class Board
{
    private Size $size;

    /**
     * @var Field[]
     */
    private array $fields;

    private Field $lastUsedField;

    /**
     * @param Field[] $fields
     */
    private function __construct(Size $size, array $fields, Field $lastUsedField)
    {
        $this->size = $size;
        $this->fields = $fields;
        $this->lastUsedField = $lastUsedField;
    }

    public static function empty(Size $size): Board
    {
        $fields = [];

        $height = $size->height();
        $width = $size->width();

        for ($y = 1; $y <= $height; $y++) {
            for ($x = 1; $x <= $width; $x++) {
                $fields[] = Field::empty(new Point($x, $y));
            }
        }

        return new self($size, $fields, $fields[0]);
    }

    /**
     * @throws ColumnAlreadyFilledException
     * @throws OutOfSizeException
     */
    public function dropStone(Stone $stone, int $column): Board
    {
        $positionOfFirstEmptyField = $this->findPositionOfFirstEmptyFieldInColumn($column);

        $fields = $this->fields;

        $field = &$fields[$positionOfFirstEmptyField];
        $field = $field->placeStone($stone);

        return new self($this->size, $fields, $field);
    }

    /**
     * @throws ColumnAlreadyFilledException
     * @throws OutOfSizeException
     */
    private function findPositionOfFirstEmptyFieldInColumn(int $column): int
    {
        if ($column < 1 || $column > $this->size->width()) {
            throw new OutOfSizeException();
        }

        $reversedFields = array_reverse($this->fields, true);

        foreach ($reversedFields as $position => $field) {
            if ($field->isEmpty() && $field->point()->x() === $column) {
                return $position;
            }
        }

        throw new ColumnAlreadyFilledException();
    }

    /**
     * @return Field[]
     */
    public function findFieldsByColumn(int $column): array
    {
        $fields = [];

        foreach ($this->fields as $field) {
            if ($field->point()->x() === $column) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * @return Field[]
     */
    public function findFieldsByRow(int $row): array
    {
        $fields = [];

        foreach ($this->fields as $field) {
            if ($field->point()->y() === $row) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * \
     *  \
     *   \
     *    \
     *
     * @return Field[]
     */
    public function findFieldsInMainDiagonalByPoint(Point $fromPoint): array
    {
        $points = [];

        $leastDifferenceToBorder = min($fromPoint->x(), $fromPoint->y()) - 1;
        $xAtBorder = $fromPoint->x() - $leastDifferenceToBorder;
        $yAtBorder = $fromPoint->y() - $leastDifferenceToBorder;

        $width = $this->size->width();
        $height = $this->size->height();

        for ($x = $xAtBorder, $y = $yAtBorder; $x <= $width && $y <= $height; $x++, $y++) {
            $points[] = new Point($x, $y);
        }

        return $this->findFieldsByPoints($points);
    }

    /**
     *    /
     *   /
     *  /
     * /
     *
     * @return Field[]
     */
    public function findFieldsInCounterDiagonalByPoint(Point $fromPoint): array
    {
        $points = [];

        $verticalDifferenceToBorder = $fromPoint->x() - 1;
        $horizontalDifferenceToBorder = -($fromPoint->y() - $this->size->height());

        $leastDifferenceToBorder = min($verticalDifferenceToBorder, $horizontalDifferenceToBorder);
        $xAtBorder = $fromPoint->x() - $leastDifferenceToBorder;
        $yAtBorder = $fromPoint->y() + $leastDifferenceToBorder;

        $width = $this->size->width();

        for ($x = $xAtBorder, $y = $yAtBorder; $x <= $width && $y > 0; $x++, $y--) {
            $points[] = new Point($x, $y);
        }

        return $this->findFieldsByPoints($points);
    }

    /**
     * @param Point[] $points
     *
     * @return Field[]
     */
    private function findFieldsByPoints(array $points): array
    {
        $fields = [];

        foreach ($points as $point) {
            foreach ($this->fields as $field) {
                if ($field->point() == $point) {
                    $fields[] = $field;
                    break;
                }
            }
        }

        return $fields;
    }

    public function size(): Size
    {
        return $this->size;
    }

    /**
     * @return Field[]
     */
    public function fields(): array
    {
        return $this->fields;
    }

    public function lastUsedField(): Field
    {
        return $this->lastUsedField;
    }
}
