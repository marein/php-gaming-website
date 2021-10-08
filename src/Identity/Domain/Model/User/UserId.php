<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\User;

use Exception;
use Gaming\Identity\Domain\Model\User\Exception\UserNotFoundException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class UserId
{
    /**
     * @var UuidInterface
     */
    private UuidInterface $userId;

    /**
     * UserId constructor.
     *
     * @param UuidInterface $uuid
     *
     * @throws UserNotFoundException
     */
    private function __construct(UuidInterface $uuid)
    {
        $this->userId = $uuid;

        // Only Uuid version 1 is a valid UserId.
        if ($uuid->getVersion() !== 1) {
            throw new UserNotFoundException();
        }
    }

    /**
     * @return UserId
     */
    public static function generate(): UserId
    {
        return new self(Uuid::uuid1());
    }

    /**
     * Create an UserId from string.
     *
     * @param string $userId
     *
     * @return UserId
     * @throws UserNotFoundException
     */
    public static function fromString(string $userId): UserId
    {
        try {
            return new self(Uuid::fromString($userId));
        } catch (Exception $exception) {
            // This occurs if the given string is an invalid Uuid, hence an invalid UserId.
            // Throw exception, that the user can't be found.
            throw new UserNotFoundException();
        }
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
