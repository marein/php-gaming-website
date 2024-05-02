<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Projection;

use Gaming\Common\EventStore\DomainEvent;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\Game;
use Gaming\ConnectFour\Application\Game\Query\Model\Game\GameStore;
use Gaming\ConnectFour\Domain\Game\Event\GameOpened;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Port\Adapter\Persistence\Repository\InMemoryCacheGameStore;

final class GameProjection implements StoredEventSubscriber
{
    private readonly GameStore $gameStore;

    public function __construct(GameStore $gameStore)
    {
        $this->gameStore = new InMemoryCacheGameStore(
            $gameStore,
            1000
        );
    }

    public function handle(DomainEvent $domainEvent): void
    {
        $content = $domainEvent->content;

        $game = match ($content::class) {
            GameOpened::class => new Game(),
            default => $this->gameStore->find(GameId::fromString($domainEvent->streamId))
        };

        $game->apply($domainEvent->content);

        $this->gameStore->persist($game);
    }

    public function commit(): void
    {
        $this->gameStore->flush();
    }
}
