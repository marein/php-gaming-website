<?php

declare(strict_types=1);

namespace Gaming\Tests\Unit\Common\Normalizer;

use Gaming\Common\Normalizer\MigratingNormalizer;
use Gaming\Common\Normalizer\Migration;
use Gaming\Common\Normalizer\Migrations;
use Gaming\Common\Normalizer\Normalizer;
use Gaming\Common\Normalizer\Test\TestMigration;
use Gaming\Common\Normalizer\Test\TestNormalizer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MigratingNormalizerTest extends TestCase
{
    #[Test]
    public function itShouldAddTheLatestVersionKeyAfterNormalization(): void
    {
        $migratingNormalizer = $this->createMigratingNormalizer(
            'array',
            [new TestMigration('m1'), new TestMigration('m2')]
        );

        self::assertEquals(
            ['language' => 'php', 'schemaVersion' => 2],
            $migratingNormalizer->normalize(['language' => 'php'], 'array')
        );
    }

    #[Test]
    public function itShouldApplyAllMigrationsBeforeDenormalizeBeginningFromTheLatest(): void
    {
        $migratingNormalizer = $this->createMigratingNormalizer(
            'array',
            [new TestMigration('m1'), new TestMigration('m2'), new TestMigration('m3')]
        );

        self::assertEquals(
            ['language' => 'php', 'm1' => true, 'm2' => true, 'm3' => true],
            $migratingNormalizer->denormalize(['language' => 'php'], 'array')
        );

        self::assertEquals(
            ['language' => 'php', 'm1' => true, 'm2' => true, 'm3' => true],
            $migratingNormalizer->denormalize(['language' => 'php', 'schemaVersion' => 0], 'array')
        );

        self::assertEquals(
            ['language' => 'php', 'm2' => true, 'm3' => true],
            $migratingNormalizer->denormalize(['language' => 'php', 'schemaVersion' => 1], 'array')
        );

        self::assertEquals(
            ['language' => 'php', 'm3' => true],
            $migratingNormalizer->denormalize(['language' => 'php', 'schemaVersion' => 2], 'array')
        );

        self::assertEquals(
            ['language' => 'php'],
            $migratingNormalizer->denormalize(['language' => 'php', 'schemaVersion' => 3], 'array')
        );
    }

    #[Test]
    public function itShouldIgnoreAllTypesWithoutMigrations(): void
    {
        $migratingNormalizer = $this->createMigratingNormalizer(
            'int',
            [new TestMigration('m1')]
        );

        self::assertEquals(
            ['language' => 'php'],
            $migratingNormalizer->normalize(['language' => 'php'], 'array')
        );

        self::assertEquals(
            ['language' => 'php'],
            $migratingNormalizer->denormalize(['language' => 'php'], 'array')
        );
    }

    #[Test]
    public function itShouldIgnoreAnyTypeButArrays(): void
    {
        $migration = $this->createMock(Migration::class);
        $migratingNormalizer = $this->createMigratingNormalizer('int', [$migration]);

        $migration->expects($this->never())->method('migrate');

        self::assertEquals(3, $migratingNormalizer->normalize(3, 'int'));

        $migratingNormalizer->denormalize(3, 'int');
    }

    /**
     * @param Migration[] $migrations
     */
    private function createMigratingNormalizer(string $typeName, array $migrations): Normalizer
    {
        return new MigratingNormalizer(
            new TestNormalizer(),
            [$typeName => new Migrations('schemaVersion', $migrations)]
        );
    }
}
