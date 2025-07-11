<?php

declare(strict_types=1);

namespace Gaming\Common\Timer;

use DateTimeImmutable;

final class GameTimer implements Timer
{
    private function __construct(
        public readonly int $remainingMs,
        public readonly int $incrementMs,
        public readonly ?int $endsAt = null
    ) {
    }

    public static function set(int $baseMs, int $incrementMs): self
    {
        return new self($baseMs, $incrementMs);
    }

    public function start(DateTimeImmutable $now = new DateTimeImmutable()): self
    {
        return new self(
            $this->remainingMs,
            $this->incrementMs,
            $now->getTimestamp() * 1000 + (int)($now->getMicrosecond() / 1000) + $this->remainingMs
        );
    }

    public function stop(DateTimeImmutable $now = new DateTimeImmutable()): self
    {
        if ($this->endsAt === null) {
            return $this;
        }

        $nowMs = $now->getTimestamp() * 1000 + (int)($now->getMicrosecond() / 1000);
        $remainingMs = max(0, $this->endsAt - $nowMs);

        return new self(
            $remainingMs > 0 ? $remainingMs + $this->incrementMs : 0,
            $this->incrementMs,
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

    public function __toString(): string
    {
        return sprintf('game:%s:%s', $this->remainingMs, $this->incrementMs);
    }
}
