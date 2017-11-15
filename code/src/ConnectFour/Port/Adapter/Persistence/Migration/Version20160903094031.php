<?php

namespace Gambling\ConnectFour\Port\Adapter\Persistence\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20160903094031 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $table = $schema->createTable('game');

        $table->addColumn('id', 'string', ['length' => 64]);
        $table->addColumn('version', 'integer');
        $table->addColumn('aggregate', 'json');

        $table->setPrimaryKey(['id']);
        $table->addIndex(['id', 'version']);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('game');
    }
}
