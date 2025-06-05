<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Timer;

use DateTimeImmutable;

final class TimePerMove implements Timer
{
    private function __construct(
        public readonly int $remainingMs,
        public readonly int $msPerMove,
        public readonly ?DateTimeImmutable $endsAt = null
    ) {
    }

    public static function set(int $secondsPerMove): self
    {
        return new self($secondsPerMove * 1000, $secondsPerMove * 1000);
    }

    public function start(DateTimeImmutable $now = new DateTimeImmutable()): self
    {
        return new self(
            $this->msPerMove,
            $this->msPerMove,
            $now->modify('+' . $this->msPerMove . ' milliseconds')
        );
    }

    public function stop(DateTimeImmutable $now = new DateTimeImmutable()): self
    {
        if ($this->endsAt === null) {
            return $this;
        }

        if ($now >= $this->endsAt) {
            throw new \Exception('timeout');
        }

        $diff = $now->diff($this->endsAt ?? $now);
        $remainingMs = $diff->m * 2630000000
            + $diff->d * 86400000
            + $diff->h * 3600000
            + $diff->i * 60000
            + $diff->s * 1000
            + (int)ceil($diff->f * 1000);

        return new self(
            $remainingMs,
            $this->msPerMove,
            null
        );
    }

    public function remainingMs(): int
    {
        return $this->remainingMs;
    }

    public function endsAt(): ?DateTimeImmutable
    {
        return $this->endsAt;
    }
}
