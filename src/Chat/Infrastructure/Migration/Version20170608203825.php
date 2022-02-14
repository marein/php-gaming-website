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
        $table->addColumn('authorId', 'string', ['length' => 36, 'fixed' => true]);
        $table->addColumn('message', 'string', ['length' => 140]);
        $table->addColumn('writtenAt', 'datetime_immutable');

        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint(
            'chat',
            ['chatId'],
            ['id']
        );
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('message');
    }
}
