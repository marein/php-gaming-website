<?php

declare(strict_types=1);

namespace Gaming\Common\Clock;

use DateTimeImmutable;
use DateTimeZone;

/**
 * This class provides a clock that can be frozen for testing purposes.
 *
 * The following article describes the problem area in detail, as there are several solutions to this problem:
 * https://lostechies.com/jimmybogard/2008/11/09/systemtime-versus-isystemclock-dependencies-revisited/.
 */
final class Clock
{
    private static ?Clock $instance = null;

    private ?DateTimeImmutable $frozenAt;

    // This class cannot be instantiated.
    private function __construct()
    {
        $this->frozenAt = null;
    }

    // This class cannot be cloned.
    private function __clone()
    {
    }

    public static function instance(): Clock
    {
        return self::$instance ??= new self();
    }

    public function now(): DateTimeImmutable
    {
        if ($this->frozenAt !== null) {
            return $this->frozenAt;
        }

        return $this->utc();
    }

    public function freeze(?DateTimeImmutable $at = null): void
    {
        $this->frozenAt = $at ?? $this->utc();
    }

    public function resume(): void
    {
        $this->frozenAt = null;
    }

    private function utc(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }
}
