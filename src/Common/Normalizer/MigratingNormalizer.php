<?php

declare(strict_types=1);

namespace Gaming\Common\Normalizer;

final class MigratingNormalizer implements Normalizer
{
    /**
     * @var array<string, Migrations>
     */
    private readonly array $typeToMigrationsMap;

    /**
     * @param iterable<string, Migrations> $typeToMigrationsMap
     */
    public function __construct(
        private readonly Normalizer $normalizer,
        iterable $typeToMigrationsMap
    ) {
        $this->typeToMigrationsMap = [...$typeToMigrationsMap];
    }

    public function normalize(mixed $value, string $typeName): mixed
    {
        $typeMigrations = $this->typeToMigrationsMap[$typeName] ?? null;
        $normalized = $this->normalizer->normalize($value, $typeName);
        if ($typeMigrations === null || !is_array($normalized)) {
            return $normalized;
        }

        $normalized[$typeMigrations->versionKey()] = $typeMigrations->latestVersion();

        return $normalized;
    }

    public function denormalize(mixed $value, string $typeName): mixed
    {
        $migrations = $this->typeToMigrationsMap[$typeName] ?? null;
        if ($migrations === null || !is_array($value)) {
            return $this->normalizer->denormalize($value, $typeName);
        }

        $currentSchemaVersion = 0;
        if (array_key_exists($migrations->versionKey(), $value)) {
            $currentSchemaVersion = (int)$value[$migrations->versionKey()];
            unset($value[$migrations->versionKey()]);
        }

        return $this->normalizer->denormalize(
            $migrations->migrate($currentSchemaVersion, $value),
            $typeName
        );
    }
}
