<?php

declare(strict_types=1);

namespace Gaming\Chat\Application;

use Exception;
use Gaming\Chat\Application\Exception\ChatNotFoundException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class ChatId
{
    private string $chatId;

    /**
     * @throws ChatNotFoundException
     */
    private function __construct(UuidInterface $uuid)
    {
        $this->chatId = $uuid->toString();

        // Only Uuid version 1 is a valid ChatId.
        if ($uuid->getVersion() !== 1) {
            throw new ChatNotFoundException();
        }
    }

    public static function generate(): ChatId
    {
        return new self(Uuid::uuid1());
    }

    /**
     * @throws ChatNotFoundException
     */
    public static function fromString(string $chatId): ChatId
    {
        try {
            return new self(Uuid::fromString($chatId));
        } catch (Exception $exception) {
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
