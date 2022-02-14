<?php

declare(strict_types=1);

namespace Gaming\Chat\Infrastructure\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20170608203652 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $table = $schema->createTable('chat');

        $table->addColumn('id', 'uuid');
        $table->addColumn('authors', 'json');

        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('chat');
    }
}
