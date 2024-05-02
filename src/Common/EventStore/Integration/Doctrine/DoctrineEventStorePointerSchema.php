<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\Integration\Doctrine;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;

final class DoctrineEventStorePointerSchema
{
    public static function up(Schema $schema, string $tableName): void
    {
        $table = $schema->createTable($tableName);

        $table->addColumn('name', Types::STRING, ['length' => 64]);
        $table->addColumn('value', Types::BIGINT, ['unsigned' => true]);

        $table->setPrimaryKey(['name']);
    }

    public static function down(Schema $schema, string $tableName): void
    {
        $schema->dropTable($tableName);
    }
}
