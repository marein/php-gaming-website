<?php
declare(strict_types=1);

namespace Gaming\Chat\Application\Query;

final class MessagesQuery
{
    /**
     * @var string
     */
    private string $chatId;

    /**
     * @var string
     */
    private string $authorId;

    /**
     * @var int
     */
    private int $offset;

    /**
     * @var int
     */
    private int $limit;

    /**
     * MessagesQuery constructor.
     *
     * @param string $chatId
     * @param string $authorId
     * @param int    $offset
     * @param int    $limit
     */
    public function __construct(string $chatId, string $authorId, int $offset, int $limit)
    {
        $this->chatId = $chatId;
        $this->authorId = $authorId;
        $this->offset = $offset;
        $this->limit = $limit;
    }

    /**
     * @return string
     */
    public function chatId(): string
    {
        return $this->chatId;
    }

    /**
     * @return string
     */
    public function authorId(): string
    {
        return $this->authorId;
    }

    /**
     * @return int
     */
    public function offset(): int
    {
        return $this->offset;
    }

    /**
     * @return int
     */
    public function limit(): int
    {
        return $this->limit;
    }
}
