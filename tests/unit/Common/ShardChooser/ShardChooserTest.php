<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Common\ShardChooser;

use Gaming\Common\ShardChooser\Integration\TestShards;
use Gaming\Common\ShardChooser\Integration\TestStorage;
use Gaming\Common\ShardChooser\ShardChooser;
use PHPUnit\Framework\TestCase;

final class ShardChooserTest extends TestCase
{
    /**
     * @test
     */
    public function itShouldSelectShard(): void
    {
        $storage = new TestStorage();
        $shards = new TestShards(
            [
                'v1' => 's1',
                'v2' => 's2',
                'v3' => 's3'
            ]
        );
        $shardChooser = new ShardChooser($storage, $shards);

        $shardChooser->select('v1');
        self::assertSame('s1', $storage->usedShard);
        $shardChooser->select('v2');
        self::assertSame('s2', $storage->usedShard);
        $shardChooser->select('v3');
        self::assertSame('s3', $storage->usedShard);
    }

    /**
     * @test
     */
    public function itShouldNotSelectShardWhenSameShardIsSelected(): void
    {
        $storage = new TestStorage();
        $shards = new TestShards(
            [
                'v1' => 's1',
                'v2' => 's1'
            ]
        );
        $shardChooser = new ShardChooser($storage, $shards);

        $shardChooser->select('v1');
        $storage->resetUsedShard();

        $shardChooser->select('v2');
        self::assertSame('', $storage->usedShard);
    }
}
