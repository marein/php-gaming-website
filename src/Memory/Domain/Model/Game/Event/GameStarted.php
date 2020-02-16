<?php
declare(strict_types=1);

namespace Gaming\Memory\Domain\Model\Game\Event;

use DateTimeImmutable;
use Gaming\Common\Clock\Clock;
use Gaming\Common\Domain\DomainEvent;
use Gaming\Memory\Domain\Model\Game\GameId;
use Gaming\Memory\Domain\Model\Game\Player;
use Gaming\Memory\Domain\Model\Game\PlayerPool;

final class GameStarted implements DomainEvent
{
    /**
     * @var GameId
     */
    private GameId $gameId;

    /**
     * @var PlayerPool
     */
    private PlayerPool $playerPool;

    /**
     * @var DateTimeImmutable
     */
    private DateTimeImmutable $occurredOn;

    /**
     * GameStarted constructor.
     *
     * @param GameId     $gameId
     * @param PlayerPool $playerPool
     */
    public function __construct(GameId $gameId, PlayerPool $playerPool)
    {
        $this->gameId = $gameId;
        $this->playerPool = $playerPool;
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
            'gameId'    => $this->gameId->toString(),
            'playerIds' => array_map(
                static function (Player $player) {
                    return $player->id();
                },
                $this->playerPool->players()
            )
        ];
    }

    /**
     * @inheritdoc
     */
    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    /**
     * @inheritdoc
     */
    public function name(): string
    {
        return 'GameStarted';
    }
}
