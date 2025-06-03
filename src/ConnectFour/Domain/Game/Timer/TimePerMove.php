<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game\Timer;

use DateTimeImmutable;

final class TimePerMove implements Timer
{
    private function __construct(
        public readonly int $msPerMove,
        public readonly ?DateTimeImmutable $endsAt = null
    ) {
    }

    public static function set(int $secondsPerMove): self
    {
        return new self($secondsPerMove * 1000);
    }

    public function start(DateTimeImmutable $now = new DateTimeImmutable()): self
    {
        return new self(
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

        return new self(
            $this->msPerMove,
            null
        );
    }

    public function remainingMs(): int
    {
        return $this->msPerMove;
    }

    public function endsAt(): ?DateTimeImmutable
    {
        return $this->endsAt;
    }
}

