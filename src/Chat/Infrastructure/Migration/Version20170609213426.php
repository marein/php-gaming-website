<?php

declare(strict_types=1);

namespace Gaming\Chat\Infrastructure\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Gaming\Common\EventStore\Integration\Doctrine\DoctrineEventStoreSchema;
use Gaming\Common\EventStore\Integration\Doctrine\IndexOption;

final class Version20170609213426 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        DoctrineEventStoreSchema::up($schema, 'event_store', IndexOption::UseOnlyAsOutbox);
    }

    public function down(Schema $schema): void
    {
        DoctrineEventStoreSchema::down($schema, 'event_store');
    }
}
