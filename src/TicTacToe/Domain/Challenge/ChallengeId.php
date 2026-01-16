<?php

declare(strict_types=1);

namespace Gaming\TicTacToe\Domain\Challenge;

use Exception;
use Gaming\TicTacToe\Domain\Challenge\Exception\ChallengeNotFoundException;
use Symfony\Component\Uid\Uuid;

final class ChallengeId
{
    private string $challengeId;

    private function __construct(Uuid $uuid)
    {
        $this->challengeId = $uuid->toRfc4122();
    }

    public static function generate(): ChallengeId
    {
        return new self(Uuid::v6());
    }

    /**
     * @throws ChallengeNotFoundException
     */
    public static function fromString(string $challengeId): ChallengeId
    {
        try {
            return new self(Uuid::fromRfc4122($challengeId));
        } catch (Exception) {
            // This occurs if the given string is an invalid Uuid, hence an invalid ChallengeId.
            // Throw exception, that the challenge can't be found.
            throw new ChallengeNotFoundException();
        }
    }

    public function toString(): string
    {
        return $this->challengeId;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
