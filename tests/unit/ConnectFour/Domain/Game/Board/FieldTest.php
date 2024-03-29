<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\ConnectFour\Domain\Game\Board;

use Gaming\ConnectFour\Domain\Game\Board\Field;
use Gaming\ConnectFour\Domain\Game\Board\Point;
use Gaming\ConnectFour\Domain\Game\Board\Stone;
use PHPUnit\Framework\TestCase;

class FieldTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldBeCreatedEmpty(): void
    {
        $field = Field::empty(new Point(0, 1));

        $this->assertTrue($field->isEmpty());
        $this->assertEquals(Stone::None, $field->stone());
    }

    /**
     * @test
     */
    public function aStoneCanBePlaced(): void
    {
        $field = Field::empty(new Point(0, 1));

        $fieldWithStone = $field->placeStone(Stone::Red);

        $this->assertFalse($fieldWithStone->isEmpty());
        $this->assertEquals(Stone::Red, $fieldWithStone->stone());
    }

    /**
     * @test
     */
    public function itCanBeTypeCastedToString(): void
    {
        $field = Field::empty(new Point(0, 1));

        $this->assertSame((string)Stone::None->value, (string)$field);

        $fieldWithStone = $field->placeStone(Stone::Red);

        $this->assertSame((string)Stone::Red->value, (string)$fieldWithStone);
    }
}
