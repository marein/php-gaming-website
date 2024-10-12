<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Event;

use Gaming\Common\Domain\DomainEvent;
use Gaming\ConnectFour\Domain\Game\Board\Field;
use Gaming\ConnectFour\Domain\Game\Board\Point;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\Player;

final class GameWon implements DomainEvent
{
    private string $gameId;

    private string $winnerPlayerId;

    /**
     * @var Point[]
     */
    private array $winningSequence;

    /**
     * @param Field[] $winningFields
     */
    public function __construct(GameId $gameId, Player $winnerPlayer, array $winningFields)
    {
        $this->gameId = $gameId->toString();
        $this->winnerPlayerId = $winnerPlayer->id();
        $this->winningSequence = array_map(static fn(Field $field) => $field->point(), $winningFields);
    }

    public function aggregateId(): string
    {
        return $this->gameId;
    }

    public function winnerPlayerId(): string
    {
        return $this->winnerPlayerId;
    }

    /**
     * @return Point[]
     */
    public function winningSequence(): array
    {
        return $this->winningSequence;
    }
}
