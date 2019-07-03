<?php
declare(strict_types=1);

namespace Gaming\ConnectFour\Domain\Game;

use Gaming\ConnectFour\Domain\Game\Exception\GameNotFoundException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class GameId
{
    /**
     * @var string
     */
    private $gameId;

    /**
     * GameId constructor.
     *
     * @param UuidInterface $uuid
     *
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

    /**
     * @return GameId
     */
    public static function generate(): GameId
    {
        return new self(Uuid::uuid1());
    }

    /**
     * Create a GameId from string.
     *
     * @param string $gameId
     *
     * @return GameId
     * @throws GameNotFoundException
     */
    public static function fromString(string $gameId): GameId
    {
        try {
            return new self(Uuid::fromString($gameId));
        } catch (\Exception $exception) {
            // This occurs if the given string is an invalid Uuid, hence an invalid GameId.
            // Throw exception, that the game can't be found.
            throw new GameNotFoundException();
        }
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return $this->gameId;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
