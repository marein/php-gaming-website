<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Closure;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Gaming\Common\Domain\Exception\ConcurrencyException;
use Gaming\Common\EventStore\DomainEvents;
use Gaming\Common\EventStore\Integration\Doctrine\DoctrineEventStore;
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
     * @param Shards<Connection> $shards
     */
    public function __construct(
        private readonly Shards $shards,
        private readonly string $tableName,
        private readonly DoctrineEventStore $eventStore,
        private readonly Normalizer $normalizer
    ) {
    }

    public function nextIdentity(): GameId
    {
        return GameId::generate();
    }

    public function add(Game $game): void
    {
        $connection = $this->shards->lookup($game->id()->toString());

        $connection->transactional(function (Connection $connection) use ($game) {
            $id = $game->id()->toString();

            $domainEvents = (new DomainEvents($id))->append(...$game->flushDomainEvents());

            $connection->insert(
                $this->tableName,
                ['id' => $id, 'aggregate' => $this->normalizeGame($game), 'version' => $domainEvents->streamVersion()],
                ['id' => 'uuid', 'aggregate' => Types::JSON, 'version' => Types::INTEGER]
            );

            $this->eventStore->withConnection($connection)->append(...$domainEvents->flush());
        });
    }

    public function update(GameId $gameId, Closure $operation): void
    {
        $connection = $this->shards->lookup($gameId->toString());

        $connection->transactional(function (Connection $connection) use ($gameId, $operation) {
            $id = $gameId->toString();
            $row = $connection->fetchAssociative(
                'SELECT * FROM ' . $this->tableName . ' g WHERE g.id = ?',
                [$id],
                ['uuid']
            ) ?: throw new GameNotFoundException();

            $game = $this->denormalizeGame($row['aggregate']);
            $operation($game);

            $domainEvents = (new DomainEvents($id, $row['version']))->append(...$game->flushDomainEvents());
            if ($row['version'] === $domainEvents->streamVersion()) {
                return;
            }

            $connection->update(
                $this->tableName,
                ['aggregate' => $this->normalizeGame($game), 'version' => $domainEvents->streamVersion()],
                ['id' => $id, 'version' => $row['version']],
                ['id' => 'uuid', 'aggregate' => Types::JSON, 'version' => Types::INTEGER]
            ) ?: throw new ConcurrencyException();

            $this->eventStore->withConnection($connection)->append(...$domainEvents->flush());
        });
    }

    public function find(GameId $gameId): GameQueryModel
    {
        $connection = $this->shards->lookup($gameId->toString());

        $domainEvents = $this->eventStore->withConnection($connection)->byStreamId(
            $gameId->toString()
        ) ?: throw new GameNotFoundException();

        $game = new GameQueryModel();
        foreach ($domainEvents as $domainEvent) {
            $game->apply($domainEvent->content);
        }

        return $game;
    }

    public function findMany(array $gameIds): array
    {
        return [];
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
