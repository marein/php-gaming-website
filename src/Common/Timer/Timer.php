<?php

declare(strict_types=1);

namespace Gaming\Common\Timer;

use DateTimeImmutable;

interface Timer
{
    public function start(DateTimeImmutable $now = new DateTimeImmutable()): self;

    public function stop(DateTimeImmutable $now = new DateTimeImmutable()): self;

    public function remainingMs(): int;

    public function endsAt(): ?int;
}
