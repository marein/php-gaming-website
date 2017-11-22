<?php

namespace Gambling\Chat\Infrastructure\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20170608203825 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $table = $schema->createTable('message');

        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('chatId', 'string');
        $table->addColumn('authorId', 'string');
        $table->addColumn('message', 'string', ['length' => 140]);

        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint(
            'chat',
            ['chatId'],
            ['id']
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('message');
    }
}
