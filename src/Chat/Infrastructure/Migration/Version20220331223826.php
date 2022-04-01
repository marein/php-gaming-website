<?php

declare(strict_types=1);

namespace Gaming\Chat\Infrastructure\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Gaming\Common\Port\Adapter\EventStore\DoctrineEventStorePointerSchema;

final class Version20220331223826 extends AbstractMigration
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
