<?php

declare(strict_types=1);

namespace Gaming\Chat\Application;

use Exception;
use Gaming\Chat\Application\Exception\ChatNotFoundException;
use Symfony\Component\Uid\Uuid;

final class ChatId
{
    private string $chatId;

    private function __construct(Uuid $uuid)
    {
        $this->chatId = $uuid->toRfc4122();
    }

    public static function generate(): ChatId
    {
        return new self(Uuid::v6());
    }

    /**
     * @throws ChatNotFoundException
     */
    public static function fromString(string $chatId): ChatId
    {
        try {
            return new self(Uuid::fromRfc4122($chatId));
        } catch (Exception) {
            // This occurs if the given string is an invalid Uuid, hence an invalid ChatId.
            // Throw exception, that the chat can't be found.
            throw new ChatNotFoundException();
        }
    }

    public function toString(): string
    {
        return $this->chatId;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
