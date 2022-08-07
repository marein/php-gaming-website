<?php

declare(strict_types=1);

namespace Gaming\Common\Normalizer;

final class Migrations
{
    /**
     * @var Migration[]
     */
    private readonly array $migrations;

    /**
     * @param iterable<Migration> $migrations
     */
    public function __construct(
        private readonly string $versionKey,
        iterable $migrations
    ) {
        $this->migrations = [...$migrations];
    }

    /**
     * @param array<string, mixed> $value
     *
     * @return array<string, mixed>
     */
    public function migrate(int $currentSchemaVersion, array $value): array
    {
        return array_reduce(
            array_slice($this->migrations, $currentSchemaVersion),
            static fn(array $carry, Migration $migration): array => $migration->migrate($carry),
            $value
        );
    }

    public function latestVersion(): int
    {
        return count($this->migrations);
    }

    public function versionKey(): string
    {
        return $this->versionKey;
    }
}
