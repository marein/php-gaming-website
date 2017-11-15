<?php

namespace Gambling\Chat\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20170608203652 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $table = $schema->createTable('chat');

        $table->addColumn('id', 'string', ['length' => 64]);
        $table->addColumn('ownerId', 'string', ['length' => 64]);
        $table->addColumn('authors', 'json');

        $table->setPrimaryKey(['id']);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('chat');
    }
}
