<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Timer;

use DateTimeImmutable;

final class TimePerGame implements Timer
{
    private function __construct(
        public readonly int $remainingMs,
        public readonly ?int $endsAt = null
    ) {
    }

    public static function set(int $remainingSeconds): self
    {
        return new self($remainingSeconds * 1000);
    }

    public function start(DateTimeImmutable $now = new DateTimeImmutable()): self
    {
        return new self(
            $this->remainingMs,
            $now->getTimestamp() * 1000 + (int)($now->getMicrosecond() / 1000) + $this->remainingMs
        );
    }

    public function stop(DateTimeImmutable $now = new DateTimeImmutable()): self
    {
        if ($this->endsAt === null) {
            return $this;
        }

        $nowMs = $now->getTimestamp() * 1000 + (int)($now->getMicrosecond() / 1000);
        if ($nowMs >= $this->endsAt) {
            throw new \Exception('timeout');
        }

        return new self(
            max(0, $this->endsAt - $nowMs),
            null
        );
    }

    public function remainingMs(): int
    {
        return $this->remainingMs;
    }

    public function endsAt(): ?int
    {
        return $this->endsAt;
    }
}
