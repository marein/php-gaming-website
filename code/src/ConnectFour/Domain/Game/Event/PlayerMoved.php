<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Event;

use Gaming\Common\Clock\Clock;
use Gaming\Common\Domain\DomainEvent;
use Gaming\ConnectFour\Domain\Game\Board\Point;
use Gaming\ConnectFour\Domain\Game\Board\Stone;
use Gaming\ConnectFour\Domain\Game\GameId;

final class PlayerMoved implements DomainEvent
{
    /**
     * @var GameId
     */
    private $gameId;

    /**
     * @var Point
     */
    private $point;

    /**
     * @var Stone
     */
    private $stone;

    /**
     * @var \DateTimeImmutable
     */
    private $occurredOn;

    /**
     * PlayerMoved constructor.
     *
     * @param GameId $gameId
     * @param Point  $point
     * @param Stone  $stone
     */
    public function __construct(GameId $gameId, Point $point, Stone $stone)
    {
        $this->gameId = $gameId;
        $this->point = $point;
        $this->stone = $stone;
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
            'gameId' => $this->gameId->toString(),
            'x'      => $this->point->x(),
            'y'      => $this->point->y(),
            'color'  => $this->stone->color()
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
        return 'PlayerMoved';
    }
}
