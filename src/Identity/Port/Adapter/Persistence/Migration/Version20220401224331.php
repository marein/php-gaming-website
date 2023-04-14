<?php

declare(strict_types=1);

namespace Gaming\Identity\Port\Adapter\Persistence\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Gaming\Common\EventStore\Integration\Doctrine\DoctrineEventStorePointerSchema;

final class Version20220401224331 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        DoctrineEventStorePointerSchema::up($schema, 'event_store_pointer');
    }

    public function down(Schema $schema): void
    {
        DoctrineEventStorePointerSchema::down($schema, 'event_store_pointer');
    }
}
