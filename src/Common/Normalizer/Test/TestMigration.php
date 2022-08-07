<?php

declare(strict_types=1);

namespace Gaming\Common\Normalizer\Test;

use Gaming\Common\Normalizer\Migration;

final class TestMigration implements Migration
{
    public function __construct(
        private readonly string $key
    ) {
    }

    public function migrate(array $value): array
    {
        $value[$this->key] = true;

        return $value;
    }
}
