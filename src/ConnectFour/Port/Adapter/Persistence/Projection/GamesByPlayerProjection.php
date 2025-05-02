<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Port\Adapter\Persistence\Projection;

use Gaming\Common\EventStore\DomainEvent;
use Gaming\Common\EventStore\NoCommit;
use Gaming\Common\EventStore\StoredEventSubscriber;
use Gaming\ConnectFour\Application\Game\Query\Model\GamesByPlayer\GamesByPlayerStore;
use Gaming\ConnectFour\Domain\Game\Event\GameAborted;
use Gaming\ConnectFour\Domain\Game\Event\GameDrawn;
use Gaming\ConnectFour\Domain\Game\Event\GameResigned;
use Gaming\ConnectFour\Domain\Game\Event\GameWon;
use Gaming\ConnectFour\Domain\Game\Event\PlayerJoined;

final class GamesByPlayerProjection implements StoredEventSubscriber
{
    use NoCommit;

    public function __construct(
        private readonly GamesByPlayerStore $gamesByPlayerStore
    ) {
    }

    public function handle(DomainEvent $domainEvent): void
    {
        $content = $domainEvent->content;

        match ($content::class) {
            PlayerJoined::class => $this->handlePlayerJoined($content),
            GameDrawn::class => $this->handleGameDrawn($content),
            GameWon::class => $this->handleGameWon($content),
            GameResigned::class => $this->handleGameResigned($content),
            GameAborted::class => $this->handleGameAborted($content),
            default => true
        };
    }

    private function handlePlayerJoined(PlayerJoined $playerJoined): void
    {
        $this->gamesByPlayerStore->addRunning(
            $playerJoined->aggregateId(),
            $playerJoined->redPlayerId,
            $playerJoined->yellowPlayerId
        );
    }

    private function handleGameDrawn(GameDrawn $gameDrawn): void
    {
        $this->gamesByPlayerStore->addDraw(
            $gameDrawn->aggregateId(),
            $gameDrawn->playerIds[0] ?? '',
            $gameDrawn->playerIds[1] ?? ''
        );
    }

    private function handleGameWon(GameWon $gameWon): void
    {
        $this->gamesByPlayerStore->addWin(
            $gameWon->aggregateId(),
            $gameWon->winnerId,
            $gameWon->loserId
        );
    }

    private function handleGameResigned(GameResigned $gameResigned): void
    {
        $this->gamesByPlayerStore->addWin(
            $gameResigned->aggregateId(),
            $gameResigned->opponentPlayerId(),
            $gameResigned->resignedPlayerId()
        );
    }

    private function handleGameAborted(GameAborted $gameAborted): void
    {
        // We're only interested in running games.
        if ($gameAborted->opponentPlayerId() === '') {
            return;
        }

        $this->gamesByPlayerStore->addAbort(
            $gameAborted->aggregateId(),
            $gameAborted->abortedPlayerId(),
            $gameAborted->opponentPlayerId()
        );
    }
}
