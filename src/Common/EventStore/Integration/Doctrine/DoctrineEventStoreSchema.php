<?php

declare(strict_types=1);

namespace Gaming\Common\EventStore\Integration\Doctrine;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;

final class DoctrineEventStoreSchema
{
    public static function up(Schema $schema, string $tableName, IndexOption $indexOption): void
    {
        $table = $schema->createTable($tableName);

        $table->addColumn('id', Types::BIGINT, ['autoincrement' => true, 'unsigned' => true]);
        $table->addColumn('streamId', 'uuid');
        $table->addColumn('streamVersion', Types::INTEGER, ['unsigned' => true]);
        $table->addColumn('content', Types::JSON);
        $table->addColumn('headers', Types::JSON);

        $table->setPrimaryKey(['id']);

        match ($indexOption) {
            IndexOption::UseOnlyAsOutbox => true,
            IndexOption::AccessByStreamId => $table->addIndex(['streamId', 'streamVersion']),
            IndexOption::EnforceUniqueStreamVersionPerStreamId => $table->addUniqueIndex(['streamId', 'streamVersion'])
        };
    }

    public static function down(Schema $schema, string $tableName): void
    {
        $schema->dropTable($tableName);
    }
}
