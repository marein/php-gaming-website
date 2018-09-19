<?php
declare(strict_types=1);

namespace Gambling\ConnectFour\Domain\Game\Event;

use Gambling\Common\Clock\Clock;
use Gambling\Common\Domain\DomainEvent;
use Gambling\ConnectFour\Domain\Game\GameId;
use Gambling\ConnectFour\Domain\Game\Player;

final class GameResigned implements DomainEvent
{
    /**
     * @var GameId
     */
    private $gameId;

    /**
     * @var Player
     */
    private $resignedPlayer;

    /**
     * @var Player
     */
    private $opponentPlayer;

    /**
     * @var \DateTimeImmutable
     */
    private $occurredOn;

    /**
     * GameResigned constructor.
     *
     * @param GameId $gameId
     * @param Player $resignedPlayer
     * @param Player $opponentPlayer
     */
    public function __construct(GameId $gameId, Player $resignedPlayer, Player $opponentPlayer)
    {
        $this->gameId = $gameId;
        $this->resignedPlayer = $resignedPlayer;
        $this->opponentPlayer = $opponentPlayer;
        $this->occurredOn = Clock::instance()->now();
    }

    /**
     * @inheritdoc
     */
    public function aggregateId(): string
    {
        return $this->gameId->toString();
    }

    /**
     * @inheritdoc
     */
    public function payload(): array
    {
        return [
            'gameId'           => $this->gameId->toString(),
            'resignedPlayerId' => $this->resignedPlayer->id(),
            'opponentPlayerId' => $this->opponentPlayer->id()
        ];
    }

    /**
     * @inheritdoc
     */
    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }

    /**
     * @inheritdoc
     */
    public function name(): string
    {
        return 'GameResigned';
    }
}
