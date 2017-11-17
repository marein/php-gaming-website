<?php

namespace Gambling\Common\ObjectMapper\Collection;

use Gambling\Common\ObjectMapper\Mapper;
use PHPUnit\Framework\TestCase;

final class ArrayObjectMapperTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldSerialize(): void
    {
        $expected = ['a', 'a', 'a'];
        $innerMapper = $this->createMock(Mapper::class);
        $innerMapper
            ->expects($this->exactly(3))
            ->method('serialize')
            ->willReturn('a');

        /** @var Mapper $innerMapper */
        $arrayMapper = new ArrayObjectMapper($innerMapper);
        $serialized = $arrayMapper->serialize(
            new \ArrayObject([1, 2, 3])
        );

        $this->assertSame($expected, $serialized);
    }

    /**
     * @test
     */
    public function itShouldDeserialize(): void
    {
        $expected = new \ArrayObject(['a', 'a', 'a']);
        $innerMapper = $this->createMock(Mapper::class);
        $innerMapper
            ->expects($this->exactly(3))
            ->method('deserialize')
            ->willReturn('a');

        /** @var Mapper $innerMapper */
        $arrayMapper = new ArrayObjectMapper($innerMapper);
        $serialized = $arrayMapper->deserialize([1, 2, 3]);

        $this->assertEquals($expected, $serialized);
    }
}
