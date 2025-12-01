<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\Bot;

use Gaming\Common\Domain\Exception\ConcurrencyException;
use Gaming\Identity\Domain\Model\Account\AccountId;
use Gaming\Identity\Domain\Model\Bot\Exception\UsernameAlreadyExistsException;

interface Bots
{
    public function nextIdentity(): AccountId;

    /**
     * Enforces uniqueness for username in the set of accounts.
     *
     * @throws ConcurrencyException
     * @throws UsernameAlreadyExistsException
     */
    public function save(Bot $bot): void;

    public function getByUsername(string $username): ?Bot;
}
