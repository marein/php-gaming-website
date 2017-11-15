<?php

namespace Gambling\ConnectFour\Domain\Game\Event;

use Gambling\Common\Domain\DomainEvent;
use Gambling\ConnectFour\Domain\Game\GameId;
use Gambling\ConnectFour\Domain\Game\Player;
use Gambling\ConnectFour\Domain\Game\Size;

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
     * @var \DateTimeImmutable
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
        $this->occurredOn = new \DateTimeImmutable();
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
    public function occurredOn(): \DateTimeImmutable
    {
        return $this->occurredOn;
    }

    /**
     * @inheritdoc
     */
    public function name(): string
    {
        return 'connect-four.game-opened';
    }
}
