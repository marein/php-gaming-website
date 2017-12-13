<?php

namespace Gambling\User\Port\Adapter\Persistence\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20170526204325 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $table = $schema->createTable('user');

        $table->addColumn('id', 'uuid_binary_ordered_time');
        $table->addColumn('credentials_username', 'string');
        $table->addColumn('credentials_password', 'string');

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['credentials_username']);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('user');
    }
}
