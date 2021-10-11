<?php

declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game;

use Exception;
use Gaming\ConnectFour\Domain\Game\Exception\GameNotFoundException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class GameId
{
    private string $gameId;

    /**
     * @throws GameNotFoundException
     */
    private function __construct(UuidInterface $uuid)
    {
        $this->gameId = $uuid->toString();

        // Only Uuid version 1 is a valid GameId.
        if ($uuid->getVersion() !== 1) {
            throw new GameNotFoundException();
        }
    }

    public static function generate(): GameId
    {
        return new self(Uuid::uuid1());
    }

    /**
     * @throws GameNotFoundException
     */
    public static function fromString(string $gameId): GameId
    {
        try {
            return new self(Uuid::fromString($gameId));
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
