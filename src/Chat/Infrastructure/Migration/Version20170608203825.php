<?php

declare(strict_types=1);

namespace Gaming\Chat\Infrastructure\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20170608203825 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $table = $schema->createTable('message');

        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('chatId', 'uuid');
        $table->addColumn('authorId', 'uuid');
        $table->addColumn('message', 'string', ['length' => 140]);
        $table->addColumn('writtenAt', 'datetime_immutable');
        $table->addColumn('idempotencyKey', 'binary', ['length' => 16, 'fixed' => true, 'notnull' => false]);

        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint(
            'chat',
            ['chatId'],
            ['id']
        );
        $table->addUniqueIndex(['idempotencyKey'], 'uniq_idempotency_key');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('message');
    }
}
