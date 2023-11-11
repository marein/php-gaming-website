<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Closure;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Gaming\Common\Domain\DomainEventPublisher;
use Gaming\Common\Domain\Exception\ConcurrencyException;
use Gaming\Common\EventStore\EventStore;
use Gaming\Common\Normalizer\Normalizer;
use Gaming\Common\Sharding\Shards;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\Game as GameQueryModel;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\GameFinder;
use Gaming\ConnectFour\Domain\Game\Exception\GameNotFoundException;
use Gaming\ConnectFour\Domain\Game\Game;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\Games;

final class DoctrineJsonGameRepository implements Games, GameFinder
{
    /**
     * @param Shards<string> $shards
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly string $tableName,
        private readonly DomainEventPublisher $domainEventPublisher,
        private readonly EventStore $eventStore,
        private readonly Normalizer $normalizer,
        private readonly Shards $shards
    ) {
    }

    public function nextIdentity(): GameId
    {
        return GameId::generate();
    }

    public function add(Game $game): void
    {
        $this->switchShard($game->id());

        $this->connection->transactional(function () use ($game) {
            $this->domainEventPublisher->publish($game->flushDomainEvents());

            $this->connection->insert(
                $this->tableName,
                ['id' => $game->id()->toString(), 'aggregate' => $this->normalizeGame($game), 'version' => 1],
                ['id' => 'uuid', 'aggregate' => Types::JSON, 'version' => Types::INTEGER]
            );
        });
    }

    public function update(GameId $gameId, Closure $operation): void
    {
        $this->switchShard($gameId);

        $this->connection->transactional(function () use ($gameId, $operation) {
            $id = $gameId->toString();
            $row = $this->connection->fetchAssociative(
                'SELECT * FROM ' . $this->tableName . ' g WHERE g.id = ?',
                [$id],
                ['uuid']
            ) ?: throw new GameNotFoundException();

            $game = $this->denormalizeGame($row['aggregate']);
            $operation($game);

            $this->domainEventPublisher->publish($game->flushDomainEvents());

            $this->connection->update(
                $this->tableName,
                ['aggregate' => $this->normalizeGame($game), 'version' => $row['version'] + 1],
                ['id' => $id, 'version' => $row['version']],
                ['id' => 'uuid', 'aggregate' => Types::JSON, 'version' => Types::INTEGER]
            ) ?: throw new ConcurrencyException();
        });
    }

    public function find(GameId $gameId): GameQueryModel
    {
        $this->switchShard($gameId);

        $domainEvents = $this->eventStore->byAggregateId(
            $gameId->toString()
        ) ?: throw new GameNotFoundException();

        $game = new GameQueryModel();
        foreach ($domainEvents as $domainEvent) {
            $game->apply($domainEvent);
        }

        return $game;
    }

    private function switchShard(GameId $gameId): void
    {
        $this->connection->executeStatement(
            'USE ' . $this->connection->quoteIdentifier($this->shards->lookup($gameId->toString()))
        );
    }

    private function normalizeGame(Game $game): mixed
    {
        return $this->normalizer->normalize($game, Game::class);
    }

    private function denormalizeGame(mixed $game): Game
    {
        return $this->normalizer->denormalize(
            json_decode($game, true, 512, JSON_THROW_ON_ERROR),
            Game::class
        );
    }
}
