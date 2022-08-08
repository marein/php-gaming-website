<?php

declare(strict_types=1);

namespace Gaming\Common\Normalizer;

interface Migration
{
    /**
     * @param array<string, mixed> $value
     *
     * @return array<string, mixed>
     */
    public function migrate(array $value): array;
}
