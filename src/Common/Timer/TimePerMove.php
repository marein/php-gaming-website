<?php

declare(strict_types=1);

namespace Gaming\Common\Timer;

use DateTimeImmutable;

final class TimePerMove implements Timer
{
    private function __construct(
        public readonly int $remainingMs,
        public readonly int $msPerMove,
        public readonly ?int $endsAt = null
    ) {
    }

    public static function set(int $secondsPerMove): self
    {
        return new self($secondsPerMove * 1000, $secondsPerMove * 1000);
    }

    public function start(DateTimeImmutable $now = new DateTimeImmutable()): self
    {
        return new self(
            $remainingMs = $this->remainingMs > 0 ? $this->msPerMove : 0,
            $this->msPerMove,
            $now->getTimestamp() * 1000 + (int)($now->getMicrosecond() / 1000) + $remainingMs
        );
    }

    public function stop(DateTimeImmutable $now = new DateTimeImmutable()): self
    {
        if ($this->endsAt === null) {
            return $this;
        }

        $nowMs = $now->getTimestamp() * 1000 + (int)($now->getMicrosecond() / 1000);

        return new self(
            $this->endsAt - $nowMs <= 0 ? 0 : $this->msPerMove,
            $this->msPerMove,
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
