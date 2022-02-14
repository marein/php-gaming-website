<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\User;

use Exception;
use Gaming\Identity\Domain\Model\User\Exception\UserNotFoundException;
use Symfony\Component\Uid\Uuid;

final class UserId
{
    private Uuid $userId;

    private function __construct(Uuid $uuid)
    {
        $this->userId = $uuid;
    }

    public static function generate(): UserId
    {
        return new self(Uuid::v6());
    }

    /**
     * @throws UserNotFoundException
     */
    public static function fromString(string $userId): UserId
    {
        try {
            return new self(Uuid::fromRfc4122($userId));
        } catch (Exception $exception) {
            // This occurs if the given string is an invalid Uuid, hence an invalid UserId.
            // Throw exception, that the user can't be found.
            throw new UserNotFoundException();
        }
    }

    public function toString(): string
    {
        return $this->userId->toRfc4122();
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
