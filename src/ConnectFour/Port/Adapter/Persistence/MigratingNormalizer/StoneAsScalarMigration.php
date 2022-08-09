<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\MigratingNormalizer;

use Gaming\Common\Normalizer\Migration;

final class StoneAsScalarMigration implements Migration
{
    public function migrate(array $value): array
    {
        // Structure in an open game.
        $stone = $value['state']['player']['stone'] ?? null;
        if ($stone !== null) {
            $value['state']['player']['stone'] = $stone['color'];
        }

        // Structure in a running game.
        $stone = $value['state']['players']['nextPlayer']['stone'] ?? null;
        if ($stone !== null) {
            $value['state']['players']['nextPlayer']['stone'] = $stone['color'];
        }

        // Structure in a running game.
        $stone = $value['state']['players']['currentPlayer']['stone'] ?? null;
        if ($stone !== null) {
            $value['state']['players']['currentPlayer']['stone'] = $stone['color'];
        }

        return $value;
    }
}
