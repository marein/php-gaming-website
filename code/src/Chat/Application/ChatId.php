<?php

namespace Gambling\Chat\Application;

use Gambling\Chat\Application\Exception\ChatNotFoundException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class ChatId
{
    /**
     * @var string
     */
    private $chatId;

    /**
     * ChatId constructor.
     *
     * @param UuidInterface $uuid
     *
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

    /**
     * @return ChatId
     */
    public static function generate()
    {
        return new self(Uuid::uuid1());
    }

    /**
     * Create a ChatId from string.
     *
     * @param string $chatId
     *
     * @return ChatId
     * @throws ChatNotFoundException
     */
    public static function fromString(string $chatId): ChatId
    {
        try {
            return new self(Uuid::fromString($chatId));
        } catch (\Exception $exception) {
            // This occurs if the given string is an invalid Uuid, hence an invalid ChatId.
            // Throw exception, that the chat can't be found.
            throw new ChatNotFoundException();
        }
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->chatId;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toString();
    }
}
