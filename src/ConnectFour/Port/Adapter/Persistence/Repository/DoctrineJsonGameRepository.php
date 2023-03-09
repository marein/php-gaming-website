<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Repository;

use Closure;
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
    public function __construct(
        private readonly Connection $connection,
        private readonly string $tableName,
        private readonly DomainEventPublisher $domainEventPublisher,
        private readonly Normalizer $normalizer
    ) {
    }

    public function nextIdentity(): GameId
    {
        return GameId::generate();
    }

    public function add(Game $game): void
    {
        $this->domainEventPublisher->publish(
            $game->flushDomainEvents()
        );

        $id = $game->id()->toString();
        $normalizedGame = $this->normalizer->normalize($game, Game::class);
        $this->connection->insert(
            $this->tableName,
            ['id' => $id, 'aggregate' => $normalizedGame, 'version' => 1],
            ['id' => 'uuid', 'aggregate' => 'json', 'version' => 'integer']
        );
    }

    public function update(GameId $gameId, Closure $operation): void
    {
        $id = $gameId->toString();
        $row = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($this->tableName, 'g')
            ->where('g.id = :id')
            ->setParameter('id', $id, 'uuid')
            ->executeQuery()
            ->fetchAssociative();
        if ($row === false) {
            throw new GameNotFoundException();
        }

        $operation(
            $game = $this->normalizer->denormalize(
                json_decode($row['aggregate'], true, 512, JSON_THROW_ON_ERROR),
                Game::class
            )
        );

        $this->domainEventPublisher->publish(
            $game->flushDomainEvents()
        );

        $normalizedGame = $this->normalizer->normalize($game, Game::class);
        $result = $this->connection->update(
            $this->tableName,
            ['aggregate' => $normalizedGame, 'version' => $row['version'] + 1],
            ['id' => $id, 'version' => $row['version']],
            ['id' => 'uuid', 'aggregate' => 'json', 'version' => 'integer']
        );

        if ($result === 0) {
            throw new ConcurrencyException();
        }
    }
}
