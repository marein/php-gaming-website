<?php

declare(strict_types=1);

namespace Gaming\Chat\Infrastructure\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Gaming\Common\Port\Adapter\EventStore\DoctrineEventStoreSchema;

final class Version20170609213426 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        DoctrineEventStoreSchema::up($schema, 'event_store');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        DoctrineEventStoreSchema::down($schema, 'event_store');
    }
}
