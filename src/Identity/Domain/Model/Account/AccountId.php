<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\Account;

use Exception;
use Gaming\Identity\Domain\Model\Account\Exception\AccountNotFoundException;
use Symfony\Component\Uid\Uuid;

final class AccountId
{
    private Uuid $accountId;

    private function __construct(Uuid $uuid)
    {
        $this->accountId = $uuid;
    }

    public static function generate(): AccountId
    {
        return new self(Uuid::v6());
    }

    /**
     * @throws AccountNotFoundException
     */
    public static function fromString(string $accountId): AccountId
    {
        try {
            return new self(Uuid::fromRfc4122($accountId));
        } catch (Exception $exception) {
            // This occurs if the given string is an invalid Uuid, hence an invalid AccountId.
            // Throw exception, that the user can't be found.
            throw new AccountNotFoundException();
        }
    }

    public function toString(): string
    {
        return $this->accountId->toRfc4122();
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
