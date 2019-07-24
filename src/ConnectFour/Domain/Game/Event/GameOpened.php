<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Event;

use DateTimeImmutable;
use Gaming\Common\Clock\Clock;
use Gaming\Common\Domain\DomainEvent;
use Gaming\ConnectFour\Domain\Game\Board\Size;
use Gaming\ConnectFour\Domain\Game\GameId;
use Gaming\ConnectFour\Domain\Game\Player;

final class GameOpened implements DomainEvent
{
    /**
     * @var GameId
     */
    private $gameId;

    /**
     * @var Size
     */
    private $size;

    /**
     * @var Player
     */
    private $player;

    /**
     * @var DateTimeImmutable
     */
    private $occurredOn;

    /**
     * GameOpened constructor.
     *
     * @param GameId $gameId
     * @param Size   $size
     * @param Player $player
     */
    public function __construct(GameId $gameId, Size $size, Player $player)
    {
        $this->gameId = $gameId;
        $this->size = $size;
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
            'width'    => $this->size->width(),
            'height'   => $this->size->height(),
            'playerId' => $this->player->id()
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
        return 'GameOpened';
    }
}
