<?php
declare(strict_types=1);

namespace Gaming\Common\Clock;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Read this before you rant on this singleton.
 *
 * This class exists only for unit testing purposes.
 * There is a good reason why I don't use another clock abstraction like https://github.com/lcobucci/clock in this
 * application. Mainly because I only have "new \DateTimeImmutable()" calls which I need to mock. I don't want
 * to provide a clock instance up to my entities. That would be the case if I used dependency injection.
 * Nearly everything would need it. That's why I treat "new \DateTimeImmutable()" as a function which I can replace.
 * In tests I can freeze the DateTime to a specific value. I've also a direct dependency to ramsey/uuid in my entities.
 * So why not this simple clock abstraction? If I need more or want an explicit clock dependency, I can still go
 * with dependency injection later on.
 *
 * There is a good read about this here
 * https://lostechies.com/jimmybogard/2008/11/09/systemtime-versus-isystemclock-dependencies-revisited/
 *
 * And yes, you would never freeze the DateTime in production.
 */
final class Clock
{
    /**
     * @var Clock|null
     */
    private static ?Clock $instance = null;

    /**
     * @var DateTimeImmutable|null
     */
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

    /**
     * @return Clock
     */
    public static function instance(): Clock
    {
        return self::$instance ??= new self();
    }

    /**
     * Get the current DateTime.
     *
     * @return DateTimeImmutable
     */
    public function now(): DateTimeImmutable
    {
        if ($this->frozenAt !== null) {
            return $this->frozenAt;
        }

        return $this->utc();
    }

    /**
     * Freeze the clock.
     *
     * @param DateTimeImmutable|null $at
     */
    public function freeze(DateTimeImmutable $at = null): void
    {
        $this->frozenAt = $at ?? $this->utc();
    }

    /**
     * Resume a frozen clock.
     */
    public function resume(): void
    {
        $this->frozenAt = null;
    }

    /**
     * Create an UTC DateTime.
     *
     * @return DateTimeImmutable
     */
    private function utc(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }
}
