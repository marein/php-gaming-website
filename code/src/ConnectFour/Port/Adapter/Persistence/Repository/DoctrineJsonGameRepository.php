<?php

namespace Gambling\ConnectFour\Port\Adapter\Persistence\Repository;

use Doctrine\DBAL\Connection;
use Gambling\Common\Domain\DomainEventPublisher;
use Gambling\Common\Domain\Exception\ConcurrencyException;
use Gambling\ConnectFour\Domain\Game\Exception\GameNotFoundException;
use Gambling\ConnectFour\Domain\Game\Game;
use Gambling\ConnectFour\Domain\Game\GameId;
use Gambling\ConnectFour\Domain\Game\Games;
use Gambling\ConnectFour\Port\Adapter\Persistence\Mapping\GameMapper;
use Gambling\ConnectFour\Port\Adapter\Persistence\Mapping\GameMapperFactory;
use Gambling\ConnectFour\Port\Adapter\Persistence\Mapping\LazyGameMapper;

final class DoctrineJsonGameRepository implements Games
{
    /**
     * The doctrine connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * This is not a real identity map as described in PoEAA.
     * I use this array to save the aggregate id and the fetched version number for
     * concurrency control. I don't want to pollute my entity with a version property.
     * This array gets never cleared so this can be a memory leak in a long running process.
     *
     * @var array
     */
    private $identityMap;

    /**
     * The domain event publisher where domain events gets published.
     *
     * @var DomainEventPublisher
     */
    private $domainEventPublisher;

    /**
     * The game mapper to serialize the game to an array structure and back.
     *
     * Use $this->gameMapper() instead of this property. The GameMapper gets lazy loaded.
     *
     * @var GameMapper
     */
    private $gameMapper;

    /**
     * The table where the game gets persisted.
     *
     * @var string
     */
    private $tableName;

    /**
     * DoctrineGameRepository constructor.
     *
     * @param Connection           $connection           The doctrine connection.
     * @param DomainEventPublisher $domainEventPublisher The domain event publisher where domain events gets published.
     */
    public function __construct(Connection $connection, DomainEventPublisher $domainEventPublisher)
    {
        $this->connection = $connection;
        $this->domainEventPublisher = $domainEventPublisher;
        $this->tableName = 'game';
        $this->identityMap = [];
    }

    /**
     * @inheritdoc
     * @throw ConcurrencyException
     */
    public function save(Game $game): void
    {
//        Currently the whole aggregate will be persisted as json in the database, even on updates.
//        However, since we have the knowledge what has happened (via domain events), we can also do
//        partial updates like below. We have to iterate over the events from $game and apply changes
//        to the database. The variables "x", "y", "color" and "my_aggregate_id" can be filled from the
//        PlayerMoved event. So, the aggregate tells us what has changed. Kind of explicit change tracking.
//
//        UPDATE
//            game
//        SET
//            aggregate = JSON_REPLACE(
//                aggregate,
//                REPLACE(
//                    JSON_SEARCH(
//                        aggregate,
//                        'one',
//                        'x|y|0'
//                    ),
//                    '"',
//                    ''
//                ),
//                'x|y|color'
//            )
//        WHERE
//            id = 'my_aggregate_id';
//
//        We can do the exact same thing if we map the $game in a relational model without an orm.
//        For example, if we have a "field" table and a player moved, we can do something like:
//
//        UPDATE field SET color = :color WHERE gameId = :gameId and x = :x and y = :y
//
//        Or you simply use an orm like doctrine with change tracking. But it pollutes the domain model a lot.
//        There are always trade-offs.

        $id = $game->id()->toString();
        $this->domainEventPublisher->publish(
            $game->flushDomainEvents()
        );

        if (isset($this->identityMap[$id])) {
            $this->update($id, $game);
        } else {
            $this->insert($id, $game);
        }
    }

    /**
     * @inheritdoc
     */
    public function get(GameId $id): Game
    {
        $builder = $this->connection->createQueryBuilder();

        $row = $builder
            ->select('*')
            ->from($this->tableName, 't')
            ->where('t.id = :id')
            ->setParameter('id', $id->toString(), 'uuid_binary_ordered_time')
            ->execute()
            ->fetch();

        if ($row === false) {
            throw new GameNotFoundException();
        }

        $gameAsArray = json_decode($row['aggregate'], true);

        $this->registerAggregateId($gameAsArray['gameId'], $row['version']);

        return $this->gameMapper()->deserialize($gameAsArray);
    }

    /**
     * Update the game in the database.
     *
     * @param string $id
     * @param Game   $game
     *
     * @throws ConcurrencyException
     */
    private function update(string $id, Game $game): void
    {
        $version = $this->identityMap[$id]['version'];

        $result = $this->connection->update($this->tableName, [
            'aggregate' => $this->gameMapper()->serialize($game),
            'version'   => $version + 1
        ], ['id' => $id, 'version' => $version], [
            'id'        => 'uuid_binary_ordered_time',
            'aggregate' => 'json',
            'version'   => 'integer'
        ]);

        if ($result === 0) {
            throw new ConcurrencyException();
        }

        $this->registerAggregateId($id, $version + 1);
    }

    /**
     * Insert the new game in the database.
     *
     * @param string $id
     * @param Game   $game
     */
    private function insert(string $id, Game $game): void
    {
        $this->connection->insert($this->tableName, [
            'id'        => $id,
            'aggregate' => $this->gameMapper()->serialize($game),
            'version'   => 1
        ], [
            'id'        => 'uuid_binary_ordered_time',
            'aggregate' => 'json',
            'version'   => 'integer'
        ]);

        $this->registerAggregateId($id, 1);
    }

    /**
     * Register game id with its version for optimistic locking.
     *
     * @param string $id
     * @param int    $version
     */
    private function registerAggregateId(string $id, int $version): void
    {
        $this->identityMap[$id] = [
            'version' => $version
        ];
    }

    /**
     * Lazy loads the GameMapper.
     *
     * @return GameMapper
     */
    private function gameMapper(): GameMapper
    {
        if (!$this->gameMapper) {
            $this->gameMapper = (new GameMapperFactory())->create();
        }

        return $this->gameMapper;
    }
}
