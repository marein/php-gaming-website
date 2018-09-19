<?php
declare(strict_types=1);

namespace Gambling\Common\Port\Adapter\EventStore;

use Doctrine\DBAL\Schema\Schema;

final class DoctrineEventStoreSchema
{
    /**
     * @param Schema $schema
     * @param string $tableName
     */
    public static function up(Schema $schema, string $tableName)
    {
        $table = $schema->createTable($tableName);

        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string');
        $table->addColumn('aggregateId', 'uuid_binary');
        $table->addColumn('payload', 'json');
        $table->addColumn('occurredOn', 'datetime_immutable');

        $table->setPrimaryKey(['id']);
        $table->addIndex(['aggregateId']);
    }

    /**
     * @param Schema $schema
     * @param string $tableName
     */
    public static function down(Schema $schema, string $tableName)
    {
        $schema->dropTable($tableName);
    }
}
