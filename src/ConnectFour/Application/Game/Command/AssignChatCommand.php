<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Application\Game\Command;

final class AssignChatCommand
{
    /**
     * @var string
     */
    private $gameId;

    /**
     * @var string
     */
    private $chatId;

    /**
     * AssignChatCommand constructor.
     *
     * @param string $gameId
     * @param string $chatId
     */
    public function __construct(string $gameId, string $chatId)
    {
        $this->gameId = $gameId;
        $this->chatId = $chatId;
    }

    /**
     * @return string
     */
    public function gameId(): string
    {
        return $this->gameId;
    }

    /**
     * @return string
     */
    public function chatId(): string
    {
        return $this->chatId;
    }
}
