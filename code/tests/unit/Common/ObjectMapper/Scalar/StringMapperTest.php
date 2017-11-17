<?php

namespace Gambling\Common\ObjectMapper\Scalar;

use PHPUnit\Framework\TestCase;

final class StringMapperTest extends TestCase
{
    /**
     * @test
     * @dataProvider valuesProvider
     *
     * @param mixed $value
     */
    public function itShouldSerialize($value): void
    {
        $mapper = new StringMapper();
        $this->assertSame(
            (string)$value,
            $mapper->serialize($value)
        );
    }

    /**
     * @test
     * @dataProvider valuesProvider
     *
     * @param mixed $value
     */
    public function itShouldDeserialize($value): void
    {
        $mapper = new StringMapper();
        $this->assertSame(
            (string)$value,
            $mapper->deserialize($value)
        );
    }

    /**
     * @return array
     */
    public function valuesProvider(): array
    {
        return [
            [1337], [13.37], ['1337'], ['foobar'], [true], [false], [null]
        ];
    }
}
