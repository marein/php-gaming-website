<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\Integration\Doctrine;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;

final class DoctrineEventStoreSchema
{
    public static function up(Schema $schema, string $tableName): void
    {
        $table = $schema->createTable($tableName);

        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true, 'unsigned' => true]);
        $table->addColumn('aggregateId', 'uuid');
        $table->addColumn('event', Types::JSON);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['aggregateId']);
    }

    public static function down(Schema $schema, string $tableName): void
    {
        $schema->dropTable($tableName);
    }
}
