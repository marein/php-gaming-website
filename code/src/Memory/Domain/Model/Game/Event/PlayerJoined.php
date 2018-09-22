<?php
declare(strict_types=1);

namespace Gambling\Memory\Domain\Model\Game\Event;

use Gambling\Common\Clock\Clock;
use Gambling\Common\Domain\DomainEvent;
use Gambling\Memory\Domain\Model\Game\GameId;
use Gambling\Memory\Domain\Model\Game\Player;

final class PlayerJoined implements DomainEvent
{
    /**
     * @var GameId
     */
    private $gameId;

    /**
     * @var Player
     */
    private $player;

    /**
     * @var \DateTimeImmutable
     */
    private $occurredOn;

    /**
     * PlayerJoined constructor.
     *
     * @param GameId $gameId
     * @param Player $player
     */
    public function __construct(GameId $gameId, Player $player)
    {
        $this->gameId = $gameId;
        $this->player = $player;
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
            'gameId'   => $this->gameId->toString(),
            'playerId' => $this->player->id()
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
        return 'PlayerJoined';
    }
}
