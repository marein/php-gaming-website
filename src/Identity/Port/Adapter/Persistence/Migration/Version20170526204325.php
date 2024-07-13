<?php

declare(strict_types=1);

namespace Gaming\Identity\Port\Adapter\Persistence\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20170526204325 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $table = $schema->createTable('user');

        $table->addColumn('id', 'uuid');
        $table->addColumn('version', 'integer', ['default' => 1]);
        $table->addColumn('email', 'string', ['notNull' => false, 'length' => 255]);
        $table->addColumn('username', 'string', ['notNull' => false, 'length' => 20]);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['email'], 'uniq_email');
        $table->addUniqueIndex(['username'], 'uniq_username');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('user');
    }
}
