<?php

declare(strict_types=1);

namespace Gaming\Identity\Port\Adapter\Persistence\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Gaming\Common\EventStore\Integration\Doctrine\DoctrineEventStoreSchema;

final class Version20170923230032 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        DoctrineEventStoreSchema::up($schema, 'event_store');
    }

    public function down(Schema $schema): void
    {
        DoctrineEventStoreSchema::down($schema, 'event_store');
    }
}
