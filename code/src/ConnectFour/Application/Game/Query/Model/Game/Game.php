<?php

namespace Gambling\ConnectFour\Application\Game\Query\Model\Game;

final class Game
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
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @var bool
     */
    private $finished;

    /**
     * @var Field[]
     */
    private $fields;

    /**
     * Game constructor.
     *
     * @param string  $gameId
     * @param string  $chatId
     * @param int     $width
     * @param int     $height
     * @param bool    $finished
     * @param Field[] $fields
     */
    public function __construct(string $gameId, string $chatId, int $width, int $height, bool $finished, array $fields)
    {
        $this->gameId = $gameId;
        $this->chatId = $chatId;
        $this->width = $width;
        $this->height = $height;
        $this->finished = $finished;
        $this->fields = $fields;
    }

    /**
     * @return string
     */
    public function id(): string
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

    /**
     * @return int
     */
    public function width(): int
    {
        return $this->width;
    }

    /**
     * @return int
     */
    public function height(): int
    {
        return $this->height;
    }

    /**
     * @return bool
     */
    public function finished(): bool
    {
        return $this->finished;
    }

    /**
     * @return Field[]
     */
    public function fields(): array
    {
        return $this->fields;
    }

    /**
     * @param \Gambling\ConnectFour\Domain\Game\Game $game
     *
     * @return Game
     */
    public static function fromGame(\Gambling\ConnectFour\Domain\Game\Game $game)
    {
        $board = $game->board();

        return new self(
            $game->id(),
            '',
            $board->size()->width(),
            $board->size()->height(),
            false,
            array_map(function (\Gambling\ConnectFour\Domain\Game\Field $field) {
                return new Field(
                    $field->point()->x(),
                    $field->point()->y(),
                    0
                );
            }, $board->fields())
        );
    }
}
