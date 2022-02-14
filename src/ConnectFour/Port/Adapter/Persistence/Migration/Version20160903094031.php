<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20160903094031 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $table = $schema->createTable('game');

        $table->addColumn('id', 'uuid');
        $table->addColumn('version', 'integer');
        $table->addColumn('aggregate', 'json');

        $table->setPrimaryKey(['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('game');
    }
}
