<?php

declare(strict_types=1);

namespace Gaming\Chat\Infrastructure\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20170608203652 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        $table = $schema->createTable('chat');

        $table->addColumn('id', 'uuid_binary_ordered_time');
        $table->addColumn('ownerId', 'string', ['length' => 36, 'fixed' => true]);
        $table->addColumn('authors', 'json');

        $table->setPrimaryKey(['id']);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        $schema->dropTable('chat');
    }
}
