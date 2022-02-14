<?php

declare(strict_types=1);

namespace Gaming\Memory\Domain\Model\Game;

use Exception;
use Gaming\Memory\Domain\Model\Game\Exception\GameNotFoundException;
use Symfony\Component\Uid\Uuid;

final class GameId
{
    private string $gameId;

    private function __construct(Uuid $uuid)
    {
        $this->gameId = $uuid->toRfc4122();
    }

    public static function generate(): GameId
    {
        return new self(Uuid::v6());
    }

    /**
     * @throws GameNotFoundException
     */
    public static function fromString(string $gameId): GameId
    {
        try {
            return new self(Uuid::fromRfc4122($gameId));
        } catch (Exception $exception) {
            // This occurs if the given string is an invalid Uuid, hence an invalid GameId.
            // Throw exception, that the game can't be found.
            throw new GameNotFoundException();
        }
    }

    public function toString(): string
    {
        return $this->gameId;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
