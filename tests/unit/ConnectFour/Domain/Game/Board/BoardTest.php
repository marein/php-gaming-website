<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game\Board;

use Codeception\Attribute\DataProvider;
use Gaming\ConnectFour\Domain\Game\Board\Board;
use Gaming\ConnectFour\Domain\Game\Board\Field;
use Gaming\ConnectFour\Domain\Game\Board\Point;
use Gaming\ConnectFour\Domain\Game\Board\Size;
use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\Exception\ColumnAlreadyFilledException;
use Gaming\ConnectFour\Domain\Game\Exception\OutOfSizeException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BoardTest extends TestCase
{
    #[Test]
    public function itShouldBeCreatedWithEmptyFields(): void
    {
        $board = $this->createBoard();
        $size = new Size(7, 6);

        $countOfFields = $size->width() * $size->height();
        $emptyFields = array_filter(
            $board->fields(),
            static fn(Field $field): bool => $field->isEmpty()
        );

        $this->assertCount($countOfFields, $emptyFields);
        $this->assertTrue($board->lastUsedField()->isEmpty());
        $this->assertEquals($size, $board->size());
        $this->assertCount(
            $countOfFields,
            $board->fields()
        );
    }

    #[Test]
    public function aStoneCanBeDropped(): void
    {
        $board = $this->createBoard();
        $boardCopy = clone $board;

        $boardWithStone = $board->dropStone(Stone::Red, 1);
        $affectedField = $boardWithStone->fields()[35];
        $this->assertEquals(Stone::Red, $affectedField->stone());
        $this->assertEquals($affectedField, $boardWithStone->lastUsedField());

        $boardWithStone = $boardWithStone->dropStone(Stone::Yellow, 1);
        $affectedField = $boardWithStone->fields()[28];
        $this->assertEquals(Stone::Yellow, $affectedField->stone());
        $this->assertEquals($affectedField, $boardWithStone->lastUsedField());

        $this->assertEquals($board, $boardCopy);
    }

    #[Test]
    public function aStoneCanNotBeDroppedWhenColumnAlreadyFilled(): void
    {
        $this->expectException(ColumnAlreadyFilledException::class);

        $board = $this->createBoard();

        $boardWithStone = $board->dropStone(Stone::Red, 1);
        $boardWithStone = $boardWithStone->dropStone(Stone::Yellow, 1);
        $boardWithStone = $boardWithStone->dropStone(Stone::Red, 1);
        $boardWithStone = $boardWithStone->dropStone(Stone::Yellow, 1);
        $boardWithStone = $boardWithStone->dropStone(Stone::Red, 1);
        $boardWithStone = $boardWithStone->dropStone(Stone::Red, 1);
        $boardWithStone->dropStone(Stone::Yellow, 1);
    }

    #[Test]
    public function itShouldThrowExceptionIfGivenColumnIsOutOfSize(): void
    {
        $this->expectException(OutOfSizeException::class);

        $board = $this->createBoard();

        $board->dropStone(Stone::Red, 8);
    }

    /**
     * @param Field[] $expectedFields
     */
    #[Test]
    #[DataProvider('columnProvider')]
    public function itShouldFindFieldsByColumn(int $column, array $expectedFields): void
    {
        $board = $this->createBoard();

        $this->assertEquals(
            $board->findFieldsByColumn($column),
            $expectedFields
        );
    }

    public function columnProvider(): array
    {
        return [
            [
                1,
                [
                    Field::empty(new Point(1, 1)),
                    Field::empty(new Point(1, 2)),
                    Field::empty(new Point(1, 3)),
                    Field::empty(new Point(1, 4)),
                    Field::empty(new Point(1, 5)),
                    Field::empty(new Point(1, 6))
                ]
            ],
            [
                5,
                [
                    Field::empty(new Point(5, 1)),
                    Field::empty(new Point(5, 2)),
                    Field::empty(new Point(5, 3)),
                    Field::empty(new Point(5, 4)),
                    Field::empty(new Point(5, 5)),
                    Field::empty(new Point(5, 6))
                ]
            ],
            [
                7,
                [
                    Field::empty(new Point(7, 1)),
                    Field::empty(new Point(7, 2)),
                    Field::empty(new Point(7, 3)),
                    Field::empty(new Point(7, 4)),
                    Field::empty(new Point(7, 5)),
                    Field::empty(new Point(7, 6))
                ]
            ]
        ];
    }

    /**
     * @param Field[] $expectedFields
     */
    #[Test]
    #[DataProvider('rowProvider')]
    public function itShouldFindFieldsByRow(int $row, array $expectedFields): void
    {
        $board = $this->createBoard();

        $this->assertEquals(
            $board->findFieldsByRow($row),
            $expectedFields
        );
    }

    public function rowProvider(): array
    {
        return [
            [
                1,
                [
                    Field::empty(new Point(1, 1)),
                    Field::empty(new Point(2, 1)),
                    Field::empty(new Point(3, 1)),
                    Field::empty(new Point(4, 1)),
                    Field::empty(new Point(5, 1)),
                    Field::empty(new Point(6, 1)),
                    Field::empty(new Point(7, 1))
                ]
            ],
            [
                3,
                [
                    Field::empty(new Point(1, 3)),
                    Field::empty(new Point(2, 3)),
                    Field::empty(new Point(3, 3)),
                    Field::empty(new Point(4, 3)),
                    Field::empty(new Point(5, 3)),
                    Field::empty(new Point(6, 3)),
                    Field::empty(new Point(7, 3))
                ]
            ],
            [
                6,
                [
                    Field::empty(new Point(1, 6)),
                    Field::empty(new Point(2, 6)),
                    Field::empty(new Point(3, 6)),
                    Field::empty(new Point(4, 6)),
                    Field::empty(new Point(5, 6)),
                    Field::empty(new Point(6, 6)),
                    Field::empty(new Point(7, 6))
                ]
            ]
        ];
    }

    /**
     * @param Field[] $expectedFields
     */
    #[Test]
    #[DataProvider('mainDiagonalProvider')]
    public function itShouldFindFieldsInMainDiagonal(Point $point, array $expectedFields): void
    {
        $board = $this->createBoard();

        $this->assertEquals(
            $board->findFieldsInMainDiagonalByPoint($point),
            $expectedFields
        );
    }

    public function mainDiagonalProvider(): array
    {
        return [
            [
                new Point(3, 2),
                [
                    Field::empty(new Point(2, 1)),
                    Field::empty(new Point(3, 2)),
                    Field::empty(new Point(4, 3)),
                    Field::empty(new Point(5, 4)),
                    Field::empty(new Point(6, 5)),
                    Field::empty(new Point(7, 6))
                ]
            ],
            [
                new Point(5, 3),
                [
                    Field::empty(new Point(3, 1)),
                    Field::empty(new Point(4, 2)),
                    Field::empty(new Point(5, 3)),
                    Field::empty(new Point(6, 4)),
                    Field::empty(new Point(7, 5))
                ]
            ],
            [
                new Point(5, 5),
                [
                    Field::empty(new Point(1, 1)),
                    Field::empty(new Point(2, 2)),
                    Field::empty(new Point(3, 3)),
                    Field::empty(new Point(4, 4)),
                    Field::empty(new Point(5, 5)),
                    Field::empty(new Point(6, 6))
                ]
            ]
        ];
    }

    /**
     * @param Field[] $expectedFields
     */
    #[Test]
    #[DataProvider('counterDiagonalProvider')]
    public function itShouldFindFieldsInCounterDiagonal(Point $point, array $expectedFields): void
    {
        $board = $this->createBoard();

        $this->assertEquals(
            $board->findFieldsInCounterDiagonalByPoint($point),
            $expectedFields
        );
    }

    public function counterDiagonalProvider(): array
    {
        return [
            [
                new Point(5, 4),
                [
                    Field::empty(new Point(3, 6)),
                    Field::empty(new Point(4, 5)),
                    Field::empty(new Point(5, 4)),
                    Field::empty(new Point(6, 3)),
                    Field::empty(new Point(7, 2))
                ]
            ],
            [
                new Point(3, 4),
                [
                    Field::empty(new Point(1, 6)),
                    Field::empty(new Point(2, 5)),
                    Field::empty(new Point(3, 4)),
                    Field::empty(new Point(4, 3)),
                    Field::empty(new Point(5, 2)),
                    Field::empty(new Point(6, 1))
                ]
            ],
            [
                new Point(5, 3),
                [
                    Field::empty(new Point(2, 6)),
                    Field::empty(new Point(3, 5)),
                    Field::empty(new Point(4, 4)),
                    Field::empty(new Point(5, 3)),
                    Field::empty(new Point(6, 2)),
                    Field::empty(new Point(7, 1))
                ]
            ]
        ];
    }

    private function createBoard(): Board
    {
        $size = new Size(7, 6);

        return Board::empty($size);
    }
}
