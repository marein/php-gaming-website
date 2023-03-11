<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Common\ShardChooser\Integration;

use Doctrine\DBAL\Connection;
use Exception;
use Gaming\Common\ShardChooser\Exception\ShardChooserException;
use Gaming\Common\ShardChooser\Integration\DoctrineStorage;
use PHPUnit\Framework\TestCase;

final class DoctrineStorageTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldExecuteStatement(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->once())
            ->method('quoteIdentifier')
            ->with('s1')
            ->willReturn('`s1`');
        $connection
            ->expects($this->once())
            ->method('executeStatement')
            ->with('USE `s1`');
        $storage = new DoctrineStorage($connection, 'USE %s');

        $storage->useShard('s1');
    }

    /**
     * @test
     */
    public function itShouldRethrowExceptions(): void
    {
        $this->expectException(ShardChooserException::class);

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->once())
            ->method('executeStatement')
            ->willThrowException(new Exception());
        $storage = new DoctrineStorage($connection, 'USE %s');

        $storage->useShard('s1');
    }
}
