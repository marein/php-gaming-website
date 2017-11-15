<?php

namespace Gambling\User\Domain\Model\User;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class UserId
{
    /**
     * @var string
     */
    private $userId;

    /**
     * @param UuidInterface $uuid
     */
    private function __construct(UuidInterface $uuid)
    {
        $this->userId = $uuid->toString();
    }

    /**
     * @return UserId
     */
    public static function generate(): UserId
    {
        return new self(Uuid::uuid4());
    }

    /**
     * @param string $taskId
     *
     * @return UserId
     */
    public static function fromString(string $taskId): UserId
    {
        return new self(Uuid::fromString($taskId));
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
