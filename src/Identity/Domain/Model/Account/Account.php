<?php

declare(strict_types=1);

namespace Gaming\Identity\Domain\Model\Account;

use Gaming\Common\EventStore\CollectsDomainEvents;
use Gaming\Common\EventStore\DomainEvent;

class Account implements CollectsDomainEvents
{
    /**
     * @var DomainEvent[]
     */
    protected array $domainEvents = [];

    /**
     * This version is for optimistic concurrency control.
     */
    private ?int $version = null;

    protected function __construct(
        protected readonly AccountId $accountId,
        protected ?string $username = null
    ) {
    }

    public function id(): AccountId
    {
        return $this->accountId;
    }

    public function username(): string
    {
        return $this->username ?? UsernameGenerator::forAccountId($this->accountId);
    }

    public function flushDomainEvents(): array
    {
        return array_splice($this->domainEvents, 0);
    }

    protected function record(object $content): void
    {
        $this->domainEvents[] = new DomainEvent($this->accountId->toString(), $content);
    }
}
