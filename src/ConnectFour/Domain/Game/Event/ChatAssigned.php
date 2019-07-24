<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Event;

use DateTimeImmutable;
use Gaming\Common\Clock\Clock;
use Gaming\Common\Domain\DomainEvent;
use Gaming\ConnectFour\Domain\Game\GameId;

final class ChatAssigned implements DomainEvent
{
    /**
     * @var GameId
     */
    private $gameId;

    /**
     * @var string
     */
    private $chatId;

    /**
     * @var DateTimeImmutable
     */
    private $occurredOn;

    /**
     * ChatAssigned constructor.
     *
     * @param GameId $gameId
     * @param string $chatId
     */
    public function __construct(GameId $gameId, string $chatId)
    {
        $this->gameId = $gameId;
        $this->chatId = $chatId;
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
            'chatId' => $this->chatId
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
        return 'ChatAssigned';
    }
}
