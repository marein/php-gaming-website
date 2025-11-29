<?php

declare(strict_types=1);

namespace Gaming\Identity\Port\Adapter\Persistence\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20170526204325 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $accountTable = $schema->createTable('account');
        $accountTable->addColumn('id', 'uuid');
        $accountTable->addColumn('type', 'string', ['length' => 255]);
        $accountTable->addColumn('version', 'integer', ['default' => 1]);
        $accountTable->addColumn('username', 'string', ['notNull' => false, 'length' => 20]);
        $accountTable->setPrimaryKey(['id']);
        $accountTable->addUniqueIndex(['username'], 'uniq_username');

        $botTable = $schema->createTable('bot');
        $botTable->addColumn('id', 'uuid');
        $botTable->setPrimaryKey(['id']);
        $botTable->addForeignKeyConstraint('account', ['id'], ['id'], ['onDelete' => 'CASCADE'], 'fk_bot_account');

        $userTable = $schema->createTable('user');
        $userTable->addColumn('id', 'uuid');
        $userTable->addColumn('email', 'string', ['notNull' => false, 'length' => 255]);
        $userTable->setPrimaryKey(['id']);
        $userTable->addForeignKeyConstraint('account', ['id'], ['id'], ['onDelete' => 'CASCADE'], 'fk_user_account');
        $userTable->addUniqueIndex(['email'], 'uniq_email');
    }

    public function down(Schema $schema): void
    {
        $schema->createTable('bot')->removeForeignKey('fk_bot_account');
        $schema->createTable('user')->removeForeignKey('fk_user_account');
        $schema->dropTable('account');
        $schema->dropTable('bot');
        $schema->dropTable('user');
    }
}
