<?php
declare(strict_types=1);

namespace Gaming\Chat\Application\Command;

final class WriteMessageCommand
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
     * @var string
     */
    private string $message;

    /**
     * WriteMessageCommand constructor.
     *
     * @param string $chatId
     * @param string $authorId
     * @param string $message
     */
    public function __construct(string $chatId, string $authorId, string $message)
    {
        $this->chatId = $chatId;
        $this->authorId = $authorId;
        $this->message = $message;
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
     * @return string
     */
    public function message(): string
    {
        return $this->message;
    }
}
