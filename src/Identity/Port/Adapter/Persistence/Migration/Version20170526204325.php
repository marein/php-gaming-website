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
        $table->addColumn('is_signed_up', 'boolean');
        $table->addColumn('credentials_username', 'string', ['notNull' => false]);
        $table->addColumn('credentials_password', 'string', ['notNull' => false]);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['credentials_username']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('user');
    }
}
