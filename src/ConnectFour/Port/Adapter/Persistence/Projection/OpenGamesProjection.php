<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Projection;

use Gaming\Common\EventStore\DomainEvent;
use Gaming\Common\EventStore\NoCommit;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGame;
use Gaming\ConnectFour\Application\Game\Query\Model\OpenGames\OpenGameStore;
use Gaming\ConnectFour\Domain\Game\Event\GameAborted;
use Gaming\ConnectFour\Domain\Game\Event\GameOpened;
use Gaming\ConnectFour\Domain\Game\Event\PlayerJoined;

final class OpenGamesProjection implements StoredEventSubscriber
{
    use NoCommit;

    public function __construct(
        private readonly OpenGameStore $openGameStore
    ) {
    }

    public function handle(DomainEvent $domainEvent): void
    {
        $content = $domainEvent->content;

        match ($content::class) {
            GameOpened::class => $this->saveGame($content->aggregateId(), $content->playerId()),
            GameAborted::class,
            PlayerJoined::class => $this->removeGame($content->aggregateId()),
            default => true
        };
    }

    private function saveGame(string $gameId, string $playerId): void
    {
        $this->openGameStore->save(
            new OpenGame($gameId, $playerId)
        );
    }

    private function removeGame(string $gameId): void
    {
        $this->openGameStore->remove($gameId);
    }
}
