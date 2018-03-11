<?php

namespace Gambling\Identity\Domain\Model\User;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class UserId
{
    /**
     * @var UuidInterface
     */
    private $userId;

    /**
     * @param UuidInterface $uuid
     */
    private function __construct(UuidInterface $uuid)
    {
        $this->userId = $uuid;
    }

    /**
     * @return UserId
     */
    public static function generate(): UserId
    {
        return new self(Uuid::uuid1());
    }

    /**
     * @param string $userId
     *
     * @return UserId
     */
    public static function fromString(string $userId): UserId
    {
        return new self(Uuid::fromString($userId));
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->userId->toString();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
