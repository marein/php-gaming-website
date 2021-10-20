<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Doctrine\DBAL\Connection;
use Gaming\Common\Domain\DomainEventPublisher;
use Gaming\Common\Domain\Exception\ConcurrencyException;
use Gaming\Common\Normalizer\Normalizer;
use Gaming\ConnectFour\Domain\Game\Exception\GameNotFoundException;
use Gaming\ConnectFour\Domain\Game\Game;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\Games;

final class DoctrineJsonGameRepository implements Games
{
    private Connection $connection;

    /**
     * The map is used to store data for optimistic locking.
     * This array gets never cleared so this can be a memory leak
     * in a long running process.
     *
     * @var array<string, array<string, mixed>>
     */
    private array $identityMap;

    private DomainEventPublisher $domainEventPublisher;

    private Normalizer $normalizer;

    private string $tableName;

    public function __construct(
        Connection $connection,
        DomainEventPublisher $domainEventPublisher,
        Normalizer $normalizer
    ) {
        $this->connection = $connection;
        $this->identityMap = [];
        $this->domainEventPublisher = $domainEventPublisher;
        $this->normalizer = $normalizer;
        $this->tableName = 'game';
    }

    public function nextIdentity(): GameId
    {
        return GameId::generate();
    }

    /**
     * @throw ConcurrencyException
     */
    public function save(Game $game): void
    {
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

        $gameAsArray = json_decode($row['aggregate'], true, 512, JSON_THROW_ON_ERROR);

        $this->registerAggregateId($id->toString(), (int)$row['version']);

        return $this->normalizer->denormalize($gameAsArray, Game::class);
    }

    /**
     * @throws ConcurrencyException
     */
    private function update(string $id, Game $game): void
    {
        $version = $this->identityMap[$id]['version'];

        $result = $this->connection->update(
            $this->tableName,
            [
                'aggregate' => $this->normalizer->normalize($game, Game::class),
                'version' => $version + 1
            ],
            ['id' => $id, 'version' => $version],
            [
                'id' => 'uuid_binary_ordered_time',
                'aggregate' => 'json',
                'version' => 'integer'
            ]
        );

        if ($result === 0) {
            throw new ConcurrencyException();
        }

        $this->registerAggregateId($id, $version + 1);
    }

    private function insert(string $id, Game $game): void
    {
        $this->connection->insert(
            $this->tableName,
            [
                'id' => $id,
                'aggregate' => $this->normalizer->normalize($game, Game::class),
                'version' => 1
            ],
            [
                'id' => 'uuid_binary_ordered_time',
                'aggregate' => 'json',
                'version' => 'integer'
            ]
        );

        $this->registerAggregateId($id, 1);
    }

    private function registerAggregateId(string $id, int $version): void
    {
        $this->identityMap[$id] = [
            'version' => $version
        ];
    }
}
