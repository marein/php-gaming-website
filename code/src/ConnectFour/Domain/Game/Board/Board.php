<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Board;

use Gaming\ConnectFour\Domain\Game\Exception\ColumnAlreadyFilledException;
use Gaming\ConnectFour\Domain\Game\Exception\OutOfSizeException;

final class Board
{
    /**
     * @var Size
     */
    private $size;

    /**
     * @var Field[]
     */
    private $fields;

    /**
     * @var Field
     */
    private $lastUsedField;

    /**
     * Board constructor.
     *
     * @param Size       $size
     * @param Field[]    $fields
     * @param Field|null $lastUsedField
     */
    private function __construct(Size $size, array $fields, Field $lastUsedField = null)
    {
        $this->size = $size;
        $this->fields = $fields;
        $this->lastUsedField = $lastUsedField;
    }

    /*************************************************************
     *                         Factory
     *************************************************************/

    /**
     * Create an empty [Board].
     *
     * @param Size $size
     *
     * @return Board
     */
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

        return new self($size, $fields);
    }

    /*************************************************************
     *                        Behaviour
     *************************************************************/

    /**
     * Drops a [Stone] in the given column.
     *
     * @param Stone $stone
     * @param int   $column
     *
     * @return Board
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

    /*************************************************************
     *                     Finder for [Field]
     *************************************************************/

    /**
     * Find position of first empty [Field] in column.
     *
     * @param int $column
     *
     * @return int
     * @throws ColumnAlreadyFilledException
     * @throws OutOfSizeException
     */
    private function findPositionOfFirstEmptyFieldInColumn(int $column): int
    {
        if ($column > $this->size->width() || $column < 1) {
            throw new OutOfSizeException();
        }

        /** @var Field[] $reversedFields */
        $reversedFields = array_reverse($this->fields, true);

        foreach ($reversedFields as $position => $field) {
            if ($field->point()->x() == $column && $field->isEmpty()) {
                return $position;
            }
        }

        throw new ColumnAlreadyFilledException();
    }

    /**
     * Find [Field]s by column.
     *
     * @param int $column
     *
     * @return Field[]
     */
    public function findFieldsByColumn(int $column): array
    {
        $fields = [];

        foreach ($this->fields as $field) {
            if ($field->point()->x() == $column) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * Find [Field]s by row.
     *
     * @param int $row
     *
     * @return Field[]
     */
    public function findFieldsByRow(int $row): array
    {
        $fields = [];

        foreach ($this->fields as $field) {
            if ($field->point()->y() == $row) {
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
     * Find [Field]s in main diagonal.
     *
     * @param Point $fromPoint
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

        $fields = $this->findFieldsByPoints($points);

        return $fields;
    }

    /**
     *    /
     *   /
     *  /
     * /
     * Find [Field]s in counter diagonal.
     *
     * @param Point $fromPoint
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

        $fields = $this->findFieldsByPoints($points);

        return $fields;
    }

    /**
     * Find [Field]s by [Point]s.
     *
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

    /*************************************************************
     *                          Getter
     *************************************************************/

    /**
     * Returns the [Size] of the [Board].
     *
     * @return Size
     */
    public function size(): Size
    {
        return $this->size;
    }

    /**
     * Returns the [Field]s of the [Board].
     *
     * @return Field[]
     */
    public function fields(): array
    {
        return $this->fields;
    }

    /**
     * Returns the last used [Field].
     *
     * @return Field|null
     */
    public function lastUsedField(): ?Field
    {
        return $this->lastUsedField;
    }
}
