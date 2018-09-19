<?php
declare(strict_types=1);

namespace Gambling\Identity\Port\Adapter\Persistence\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Gambling\Common\Port\Adapter\EventStore\DoctrineEventStoreSchema;

final class Version20170923230032 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        DoctrineEventStoreSchema::up($schema, 'event_store');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        DoctrineEventStoreSchema::down($schema, 'event_store');
    }
}
